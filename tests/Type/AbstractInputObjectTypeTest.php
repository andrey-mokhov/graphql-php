<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Type;

use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;
use Andi\GraphQL\Exception\CantResolveInputObjectFieldException;
use Andi\GraphQL\Field\AbstractInputObjectField;
use Andi\GraphQL\Field\InputObjectField;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\InputObjectFieldMiddleware;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\Next;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\WebonyxInputObjectFieldMiddleware;
use Andi\GraphQL\Type\AbstractInputObjectType;
use Andi\GraphQL\Type\AbstractType;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractInputObjectType::class)]
#[CoversClass(InputObjectField::class)]
#[CoversClass(AbstractInputObjectField::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(InputObjectFieldMiddleware::class)]
#[UsesClass(InputObjectFieldResolver::class)]
#[UsesClass(AbstractType::class)]
#[UsesClass(Next::class)]
#[UsesClass(WebonyxInputObjectFieldMiddleware::class)]
#[UsesClass(LazyType::class)]
final class AbstractInputObjectTypeTest extends TestCase
{
    private InputObjectFieldResolverInterface $inputObjectFieldResolver;

    protected function setUp(): void
    {
        $typeRegistry = new TypeRegistry();

        $this->inputObjectFieldResolver = new InputObjectFieldResolver();
        $this->inputObjectFieldResolver->pipe(new InputObjectFieldMiddleware($typeRegistry));
        $this->inputObjectFieldResolver->pipe(new WebonyxInputObjectFieldMiddleware());
    }

    public function testInstanceOf(): void
    {
        $instance = new class extends AbstractInputObjectType {};

        self::assertInstanceOf(InputObjectTypeInterface::class, $instance);
    }

    #[DataProvider('getData')]
    public function testConfig(array $expected, array|AbstractInputObjectType $config, string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $instance = $config instanceof AbstractInputObjectType
            ? $config
            : $this->makeInputObjectType($config);

        if ($expected['name']) {
            self::assertSame($expected['name'], $instance->getName());
        }

        self::assertSame($expected['description'] ?? null, $instance->getDescription());

        if (isset($expected['parseValue'])) {
            self::assertInstanceOf(ParseValueAwareInterface::class, $instance);
            self::assertSame($expected['parseValue'], $instance::parseValue([]));
        }

        if (isset($expected['fields'])) {
            $idx = 0;
            foreach ($instance->getFields() as $fieldDefinition) {
                $expField = $expected['fields'][$idx++];

                $field = $this->inputObjectFieldResolver->resolve($fieldDefinition);

                self::assertInstanceOf(Webonyx\InputObjectField::class, $field);

                if (isset($expField['name'])) {
                    self::assertSame($expField['name'], $field->name);
                }

                if (isset($expField['description'])) {
                    self::assertSame($expField['description'], $field->description);
                }

                if (isset($expField['deprecationReason'])) {
                    self::assertTrue($field->isDeprecated());
                    self::assertSame($expField['deprecationReason'], $field->deprecationReason);
                } else {
                    self::assertFalse($field->isDeprecated());
                }

                if (isset($expField['defaultValue']) || array_key_exists('defaultValue', $expField)) {
                    self::assertTrue($field->defaultValueExists());
                    self::assertSame($expField['defaultValue'], $field->defaultValue);
                } else {
                    self::assertFalse($field->defaultValueExists());
                }

                if (isset($expField['type'])) {
                    self::assertSame($expField['type'], (string) $field->getType());
                }
            }

            self::assertCount($idx, $expected['fields']);
        }
    }

    public static function getData(): iterable
    {
        yield 'annotation InputObjectType' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'InputObjectType description',
            ],
            'config' => [
                'name' => 'foo',
                'description' => 'InputObjectType description',
            ],
        ];

        yield 'InputObjectType with method parseValue' => [
            'expected' => [
                'name' => 'foo',
                'parseValue' => 'parsed value'
            ],
            'config' => new class extends AbstractInputObjectType implements ParseValueAwareInterface {
                protected string $name = 'foo';

                public static function parseValue(array $values): mixed
                {
                    return 'parsed value';
                }
            },
        ];

        yield 'InputObjectType with one webonyx field' => [
            'expected' => [
                'name' => 'FooType',
                'fields' => [
                    [
                        'name' => 'foo',
                        'description' => 'foo description',
                        'deprecationReason' => 'foo deprecation reason',
                        'type' => 'String',
                    ],
                ],
            ],
            'config' => [
                'name' => 'FooType',
                'fields' => [
                    new Webonyx\InputObjectField([
                        'name' => 'foo',
                        'description' => 'foo description',
                        'deprecationReason' => 'foo deprecation reason',
                        'type' => Webonyx\Type::string(),
                    ]),
                ],
            ],
        ];

        yield 'InputObjectType with one native field' => [
            'expected' => [
                'name' => 'FooType',
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'String!',
                        'description' => 'foo description',
                        'deprecationReason' => 'foo deprecation reason',
                    ],
                ],
            ],
            'config' => [
                'name' => 'FooType',
                'fields' => [
                    new InputObjectField(
                        name: 'foo',
                        type: 'String',
                        mode: TypeAwareInterface::IS_REQUIRED,
                        description: 'foo description',
                        deprecationReason: 'foo deprecation reason',
                    ),
                ],
            ],
        ];

        yield 'InputObjectType with one simple field (int)' => [
            'expected' => [
                'name' => 'FooType',
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                    ],
                ],
            ],
            'config' => [
                'name' => 'FooType',
                'fields' => [
                    'foo' => 'Int',
                ],
            ],
        ];

        yield 'InputObjectType with one simple field (boolean)' => [
            'expected' => [
                'name' => 'FooType',
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Boolean',
                    ],
                ],
            ],
            'config' => [
                'name' => 'FooType',
                'fields' => [
                    'foo' => Webonyx\BooleanType::class,
                ],
            ],
        ];

        yield 'InputObjectType with one configured field' => [
            'expected' => [
                'name' => 'FooType',
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Boolean',
                    ],
                ],
            ],
            'config' => [
                'name' => 'FooType',
                'fields' => [
                    'foo' => [
                        'type' => Webonyx\BooleanType::class,
                    ],
                ],
            ],
        ];

        yield 'InputObjectType with one configured field (ignore field key)' => [
            'expected' => [
                'name' => 'FooType',
                'fields' => [
                    [
                        'name' => 'bar',
                        'type' => 'Boolean',
                    ],
                ],
            ],
            'config' => [
                'name' => 'FooType',
                'fields' => [
                    'foo' => [
                        'name' => 'bar',
                        'type' => Webonyx\BooleanType::class,
                    ],
                ],
            ],
        ];

        yield 'InputObjectType with one configured field (with all options)' => [
            'expected' => [
                'name' => 'FooType',
                'description' => 'FooType description',
                'fields' => [
                    [
                        'name' => 'bar',
                        'type' => 'Boolean!',
                        'description' => 'bar description',
                        'deprecationReason' => 'do not use it field',
                        'defaultValue' => true,
                    ],
                ],
            ],
            'config' => [
                'name' => 'FooType',
                'description' => 'FooType description',
                'fields' => [
                    [
                        'name' => 'bar',
                        'type' => Webonyx\BooleanType::class,
                        'mode' => TypeAwareInterface::IS_REQUIRED,
                        'description' => 'bar description',
                        'defaultValue' => true,
                        'deprecationReason' => 'do not use it field',
                    ],
                ],
            ],
        ];

        yield 'raise exception when configuration fields is wrong' => [
            'expected' => [
                'name' => 'foo',
                'fields' => [
                    'foo' => 'exception',
                ],
            ],
            'config' => [
                'name' => 'foo',
                'fields' => [
                    'foo' => new \stdClass(),
                ],
            ],
            'exception' => CantResolveInputObjectFieldException::class,
        ];
    }

    private function makeInputObjectType(array $config): AbstractInputObjectType
    {
        return new class($config) extends AbstractInputObjectType {
            public function __construct(array $config)
            {
                if (isset($config['name'])) {
                    $this->name = $config['name'];
                }

                if (isset($config['description'])) {
                    $this->description = $config['description'];
                }

                if (isset($config['fields'])) {
                    $this->fields = $config['fields'];
                }
            }
        };
    }
}

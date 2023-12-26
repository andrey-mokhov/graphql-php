<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Type;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentMiddleware;
use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use Andi\GraphQL\Exception\CantResolveObjectFieldException;
use Andi\GraphQL\Field\AbstractAnonymousObjectField;
use Andi\GraphQL\Field\AbstractObjectField;
use Andi\GraphQL\ObjectFieldResolver\Middleware as Objects;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\Type\AbstractInterfaceType;
use Andi\GraphQL\Type\AbstractType;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractInterfaceType::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(ObjectFieldResolver::class)]
#[UsesClass(ArgumentMiddleware::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(Objects\ObjectFieldMiddleware::class)]
#[UsesClass(Objects\WebonyxObjectFieldMiddleware::class)]
#[UsesClass(Objects\Next::class)]
#[UsesClass(AbstractObjectField::class)]
#[UsesClass(AbstractAnonymousObjectField::class)]
#[UsesClass(LazyType::class)]
#[UsesClass(AbstractType::class)]
final class AbstractInterfaceTypeTest extends TestCase
{
    private ObjectFieldResolverInterface $objectFieldResolver;

    protected function setUp(): void
    {
        $typeRegistry = new TypeRegistry();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ArgumentMiddleware($typeRegistry));
        $argumentResolver->pipe(new ArgumentConfigurationMiddleware());

        $this->objectFieldResolver = new ObjectFieldResolver();
        $this->objectFieldResolver->pipe(new Objects\ObjectFieldMiddleware($typeRegistry, $argumentResolver));
        $this->objectFieldResolver->pipe(new Objects\WebonyxObjectFieldMiddleware());
    }

    public function testInstanceOf(): void
    {
        $instance = new class extends AbstractInterfaceType {};

        self::assertInstanceOf(InterfaceTypeInterface::class, $instance);
    }


    #[DataProvider('getData')]
    public function testConfig(array $expected, array|AbstractInterfaceType $config, string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $instance = $config instanceof AbstractInterfaceType
            ? $config
            : $this->makeInterfaceType($config);

        if ($expected['name']) {
            self::assertSame($expected['name'], $instance->getName());
        }

        if (isset($expected['description']) || \array_key_exists('description', $expected)) {
            self::assertSame($expected['description'], $instance->getDescription());
        }

        if (isset($expected['fields'])) {
            $idx = 0;
            foreach ($instance->getFields() as $fieldDefinition) {
                $expField = $expected['fields'][$idx++];

                $field = $this->objectFieldResolver->resolve($fieldDefinition);

                self::assertInstanceOf(Webonyx\FieldDefinition::class, $field);

                if (isset($expField['name'])) {
                    self::assertSame($expField['name'], $field->getName());
                }

                if (isset($expField['description']) || \array_key_exists('description', $expField)) {
                    self::assertSame($expField['description'], $field->description);
                }

                if (isset($expField['deprecationReason'])) {
                    self::assertTrue($field->isDeprecated());
                    self::assertSame($expField['deprecationReason'], $field->deprecationReason);
                } else {
                    self::assertFalse($field->isDeprecated());
                }

                if (isset($expField['type'])) {
                    self::assertSame($expField['type'], (string) $field->getType());
                }

                if (isset($expField['arguments'])) {
                    foreach ($expField['arguments'] as $name => $expArg) {
                        $arg = $field->getArg($name);

                        if (isset($expArg['description'])) {
                            self::assertSame($expArg['description'], $arg->description);
                        }

                        if (isset($expArg['deprecationReason'])) {
                            self::assertTrue($arg->isDeprecated());
                            self::assertSame($expArg['deprecationReason'], $arg->deprecationReason);
                        } else {
                            self::assertFalse($arg->isDeprecated());
                        }

                        if (isset($expArg['defaultValue']) || \array_key_exists('defaultValue', $expArg)) {
                            self::assertTrue($arg->defaultValueExists());
                            self::assertSame($expArg['defaultValue'], $arg->defaultValue);
                        } else {
                            self::assertFalse($arg->defaultValueExists());
                        }

                        if (isset($expArg['type'])) {
                            self::assertSame($expArg['type'], (string) $arg->getType());
                        }
                    }
                }
            }

            self::assertCount($idx, $expected['fields']);
        }
    }

    public static function getData(): iterable
    {
        yield 'annotation InterfaceType' => [
            'expected' => [
                'name' => 'Foo name',
                'description' => 'Foo description',
            ],
            'config' => [
                'name' => 'Foo name',
                'description' => 'Foo description',
            ],
        ];

        yield 'WebonyxFieldDefinition' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'description' => 'field description',
                        'deprecationReason' => 'deprecation reason',
                        'type' => 'String',
                    ],
                ],
            ],
            'config' => [
                'fields' => [
                    new Webonyx\FieldDefinition([
                        'name' => 'foo',
                        'description' => 'field description',
                        'deprecationReason' => 'deprecation reason',
                        'type' => Webonyx\Type::string(),
                    ]),
                ],
            ],
        ];

        yield 'InterfaceType with additional fields' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'String',
                    ],
                ],
            ],
            'config' => [
                'additionalFields' => [
                    new Webonyx\FieldDefinition([
                        'name' => 'foo',
                        'type' => Webonyx\Type::string(),
                    ]),
                ],
            ],
        ];

        yield 'InterfaceType with once simple field' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                    ]
                ],
            ],
            'config' => [
                'fields' => [
                    'foo' => Webonyx\IntType::class,
                ],
            ],
        ];

        yield 'InterfaceType with once field' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int!',
                    ]
                ],
            ],
            'config' => [
                'fields' => [
                    'foo' => [
                        'type' => Webonyx\IntType::class,
                        'mode' => TypeAwareInterface::IS_REQUIRED,
                    ],
                ],
            ],
        ];

        yield 'raise exception when name of ObjectField no defined' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int!',
                    ]
                ],
            ],
            'config' => [
                'fields' => [
                    [
                        'type' => Webonyx\IntType::class,
                        'mode' => TypeAwareInterface::IS_REQUIRED,
                    ],
                ],
            ],
            'exception' => CantResolveObjectFieldException::class,
        ];

        yield 'raise exception when type of ObjectField no defined' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int!',
                    ]
                ],
            ],
            'config' => [
                'fields' => [
                    'foo' => [],
                ],
            ],
            'exception' => CantResolveObjectFieldException::class,
        ];

        yield 'raise exception when ObjectField has wrong configuration' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int!',
                    ]
                ],
            ],
            'config' => [
                'fields' => [
                    'foo' => null,
                ],
            ],
            'exception' => CantResolveObjectFieldException::class,
        ];
    }

    private function makeInterfaceType(array $config): AbstractInterfaceType
    {
        return new class ($config) extends AbstractInterfaceType {
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

                if (isset($config['additionalFields'])) {
                    foreach ($config['additionalFields'] as $field) {
                        $this->addAdditionalField($field);
                    }
                }
            }
        };
    }
}

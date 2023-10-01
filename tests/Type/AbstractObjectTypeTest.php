<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Type;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentMiddleware;
use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Exception\CantResolveObjectFieldException;
use Andi\GraphQL\Field\AbstractAnonymousObjectField;
use Andi\GraphQL\Field\AbstractObjectField;
use Andi\GraphQL\Field\AnonymousComplexityAwareTrait;
use Andi\GraphQL\Field\AnonymousResolveAwareTrait;
use Andi\GraphQL\ObjectFieldResolver\Middleware as Objects;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\Type\AbstractObjectType;
use Andi\GraphQL\Type\AbstractType;
use Andi\GraphQL\TypeRegistry;
use Andi\Tests\GraphQL\Fixture\SimpleInterfaceType;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractObjectType::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(ArgumentMiddleware::class)]
#[UsesClass(ObjectFieldResolver::class)]
#[UsesClass(Objects\ObjectFieldMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(AbstractType::class)]
#[UsesClass(Objects\Next::class)]
#[UsesClass(Objects\WebonyxObjectFieldMiddleware::class)]
#[UsesClass(LazyType::class)]
#[UsesClass(AbstractAnonymousObjectField::class)]
#[UsesClass(AbstractObjectField::class)]
#[UsesClass(AnonymousResolveAwareTrait::class)]
#[UsesClass(AnonymousComplexityAwareTrait::class)]
final class AbstractObjectTypeTest extends TestCase
{
    private ObjectFieldResolverInterface $objectFieldResolver;
    private array $resolveArgs;

    protected function setUp(): void
    {
        $typeRegistry = new TypeRegistry();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ArgumentMiddleware($typeRegistry));
        $argumentResolver->pipe(new ArgumentConfigurationMiddleware());

        $this->objectFieldResolver = new ObjectFieldResolver();
        $this->objectFieldResolver->pipe(new Objects\ObjectFieldMiddleware($typeRegistry, $argumentResolver));
        $this->objectFieldResolver->pipe(new Objects\WebonyxObjectFieldMiddleware());

        $this->resolveArgs = [null, [], null, \Mockery::mock(Webonyx\ResolveInfo::class)];
    }

    public function testInstanceOf(): void
    {
        $instance = new class extends AbstractObjectType {};

        self::assertInstanceOf(ObjectTypeInterface::class, $instance);
    }

    #[DataProvider('getData')]
    public function testConfig(array $expected, array|AbstractObjectType $config, string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $instance = $config instanceof AbstractObjectType
            ? $config
            : $this->makeObjectType($config);

        if ($expected['name']) {
            self::assertSame($expected['name'], $instance->getName());
        }

        if (isset($expected['description']) || array_key_exists('description', $expected)) {
            self::assertSame($expected['description'], $instance->getDescription());
        }

        if (isset($expected['interfaces'])) {
            $interfaces = $instance->getInterfaces();
            $interfaces = $interfaces instanceof \Traversable
                ? iterator_to_array($interfaces)
                : $interfaces;
            self::assertSame($expected['interfaces'], $interfaces);
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

                if (isset($expField['description']) || array_key_exists('description', $expField)) {
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

                        if (isset($expArg['defaultValue']) || array_key_exists('defaultValue', $expArg)) {
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

                if (isset($expField['resolve'])) {
                    self::assertIsCallable($field->resolveFn);
                    self::assertSame($expField['resolve'], call_user_func_array($field->resolveFn, $this->resolveArgs));
                } else {
                    self::assertNull($field->resolveFn);
                }

                if (isset($expField['complexity'])) {
                    self::assertIsCallable($field->complexityFn);
                    self::assertSame($expField['complexity'], call_user_func($field->complexityFn, 1, []));
                } else {
                    self::assertNull($field->complexityFn);
                }
            }

            self::assertCount($idx, $expected['fields']);
        }
    }

    public static function getData(): iterable
    {
        yield 'annotation ObjectType' => [
            'expected' => [
                'name' => 'Foo name',
                'description' => 'Foo description',
                'interfaces' => [SimpleInterfaceType::class],
            ],
            'config' => [
                'name' => 'Foo name',
                'description' => 'Foo description',
                'interfaces' => [SimpleInterfaceType::class],
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
                        'resolve' => 'foo result',
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
                        'resolve' => fn() => 'foo result',
                    ]),
                ],
            ],
        ];

        yield 'ObjectType with additional fields' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'String',
                        'resolve' => 'foo result',
                    ],
                ],
            ],
            'config' => [
                'additionalFields' => [
                    new Webonyx\FieldDefinition([
                        'name' => 'foo',
                        'type' => Webonyx\Type::string(),
                        'resolve' => fn() => 'foo result',
                    ]),
                ],
            ],
        ];

        yield 'ObjectType with once simple field' => [
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

        yield 'ObjectType with once field' => [
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
                        'typeMode' => TypeAwareInterface::IS_REQUIRED,
                    ],
                ],
            ],
        ];

        yield 'ObjectType with once field, resolve as closure' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int!',
                        'resolve' => 17,
                    ]
                ],
            ],
            'config' => [
                'fields' => [
                    'foo' => [
                        'type' => Webonyx\IntType::class,
                        'typeMode' => TypeAwareInterface::IS_REQUIRED,
                        'resolve' => fn() => 17,
                    ],
                ],
            ],
        ];

        yield 'ObjectType with once field, complexity as closure' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int!',
                        'complexity' => 12,
                    ]
                ],
            ],
            'config' => [
                'fields' => [
                    'foo' => [
                        'type' => Webonyx\IntType::class,
                        'typeMode' => TypeAwareInterface::IS_REQUIRED,
                        'complexity' => fn() => 12,
                    ],
                ],
            ],
        ];

        yield 'ObjectType with once field, resolve & complexity as closure' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int!',
                        'resolve' => 23,
                        'complexity' => 24,
                    ]
                ],
            ],
            'config' => [
                'fields' => [
                    'foo' => [
                        'type' => Webonyx\IntType::class,
                        'typeMode' => TypeAwareInterface::IS_REQUIRED,
                        'resolve' => fn() => 23,
                        'complexity' => fn() => 24,
                    ],
                ],
            ],
        ];

        yield 'ObjectType with once field, resolve as callable' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'resolve' => 34,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [
                    'foo' => [
                        'type' => 'Int',
                        'resolve' => [self::class, 'bar'],
                    ],
                ];

                public static function bar(): int
                {
                    return 34;
                }
            },
        ];

        yield 'ObjectType with once field, complexity as callable' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'complexity' => 35,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [
                    'foo' => [
                        'type' => 'Int',
                        'complexity' => [self::class, 'complexity'],
                    ],
                ];

                public static function complexity(): int
                {
                    return 35;
                }
            },
        ];

        yield 'ObjectType with once field, resolve & complexity as callable' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'resolve' => 45,
                        'complexity' => 46,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [];

                public function __construct()
                {
                    $this->fields['foo'] = [
                        'type' => 'Int',
                        'resolve' => [$this, 'resolve'],
                        'complexity' => [$this, 'complexity'],
                    ];
                }

                public function resolve(): int
                {
                    return 45;
                }

                public function complexity(): int
                {
                    return 46;
                }
            },
        ];

        yield 'ObjectType with once field, resolve as callable array' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'resolve' => 56,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [
                    'foo' => [
                        'type' => 'Int',
                        'resolve' => [self::class, 'bar'],
                    ],
                ];

                private function bar(): int
                {
                    return 56;
                }
            },
        ];

        yield 'ObjectType with once field, complexity as callable array' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'complexity' => 57,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [
                    'foo' => [
                        'type' => 'Int',
                        'complexity' => [self::class, 'complexity'],
                    ],
                ];

                private function complexity(): int
                {
                    return 57;
                }
            },
        ];

        yield 'ObjectType with once field, resolve & complexity as callable array' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'resolve' => 58,
                        'complexity' => 59,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [];

                public function __construct()
                {
                    $this->fields['foo'] = [
                        'type' => 'Int',
                        'resolve' => [$this, 'resolve'],
                        'complexity' => [$this, 'complexity'],
                    ];
                }

                private function resolve(): int
                {
                    return 58;
                }

                private function complexity(): int
                {
                    return 59;
                }
            },
        ];

        yield 'ObjectType with once field, resolve as string' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'resolve' => 67,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [
                    'foo' => [
                        'type' => 'Int',
                        'resolve' => 'resolve',
                    ],
                ];

                private function resolve(): int
                {
                    return 67;
                }
            },
        ];

        yield 'ObjectType with once field, complexity as string' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'complexity' => 68,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [
                    'foo' => [
                        'type' => 'Int',
                        'complexity' => 'complexity',
                    ],
                ];

                private function complexity(): int
                {
                    return 68;
                }
            },
        ];

        yield 'ObjectType with once field, resolve & complexity as string' => [
            'expected' => [
                'fields' => [
                    [
                        'name' => 'foo',
                        'type' => 'Int',
                        'resolve' => 68,
                        'complexity' => 69,
                    ]
                ],
            ],
            'config' => new class extends AbstractObjectType {
                protected iterable $fields = [
                    'foo' => [
                        'type' => 'Int',
                        'resolve' => self::class . '::resolve',
                        'complexity' => self::class . '::complexity',
                    ],
                ];

                private function resolve(): int
                {
                    return 68;
                }

                private function complexity(): int
                {
                    return 69;
                }
            },
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
                        'typeMode' => TypeAwareInterface::IS_REQUIRED,
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

        yield 'raise exception when ObjectField has resolve with no callable array' => [
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
                    'foo' => [
                        'type' => 'Int',
                        'resolve' => [1],
                    ],
                ],
            ],
            'exception' => CantResolveObjectFieldException::class,
        ];

        yield 'raise exception when ObjectField has resolve with no callable array 2' => [
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
                    'foo' => [
                        'type' => 'Int',
                        'resolve' => [self::class, 'unknown'],
                    ],
                ],
            ],
            'exception' => CantResolveObjectFieldException::class,
        ];

        yield 'raise exception when ObjectField has resolve with no callable string' => [
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
                    'foo' => [
                        'type' => 'Int',
                        'resolve' => 'unknown',
                    ],
                ],
            ],
            'exception' => CantResolveObjectFieldException::class,
        ];

        yield 'raise exception when ObjectField has resolve with wrong config' => [
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
                    'foo' => [
                        'type' => 'Int',
                        'resolve' => new \stdClass(),
                    ],
                ],
            ],
            'exception' => CantResolveObjectFieldException::class,
        ];
    }

    private function makeObjectType(array $config): AbstractObjectType
    {
        return new class ($config) extends AbstractObjectType {
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

                if (isset($config['interfaces'])) {
                    $this->interfaces = $config['interfaces'];
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

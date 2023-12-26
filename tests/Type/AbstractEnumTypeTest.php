<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Type;

use Andi\GraphQL\Definition\Type\EnumTypeInterface;
use Andi\GraphQL\Exception\CantResolveEnumTypeException;
use Andi\GraphQL\Field\EnumValue;
use Andi\GraphQL\Type\AbstractEnumType;
use Andi\GraphQL\TypeResolver\Middleware\GraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\Next;
use Andi\GraphQL\TypeResolver\TypeResolver;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(AbstractEnumType::class)]
#[CoversClass(EnumValue::class)]
#[UsesClass(TypeResolver::class)]
#[UsesClass(GraphQLTypeMiddleware::class)]
#[UsesClass(Next::class)]
final class AbstractEnumTypeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TypeResolverInterface $resolver;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->resolver = new TypeResolver();
        $this->resolver->pipe(new GraphQLTypeMiddleware($this->container = \Mockery::mock(ContainerInterface::class)));
    }

    public function testInstanceOf(): void
    {
        $instance = new class extends AbstractEnumType{};
        self::assertInstanceOf(EnumTypeInterface::class, $instance);
    }

    #[DataProvider('getData')]
    public function testConfig(array $expected, array $config, string $exception = null): void
    {
        if (null !== $exception) {
            $this->expectException($exception);
        }

        $object = $this->getEnumType($config);
        $this->container->shouldReceive('get')->once()->andReturn($object);

        $instance = $this->resolver->resolve(AbstractEnumType::class);
        self::assertInstanceOf(Webonyx\EnumType::class, $instance);

        self::assertSame($expected['name'], $instance->name());
        self::assertSame($expected['description'] ?? null, $instance->description());

        self::assertCount(\count($expected['values'] ?? []), $instance->getValues());
        foreach ($expected['values'] as $expValueName => $expEnumValue) {
            $enumValue = $instance->getValue($expValueName);
            self::assertInstanceOf(Webonyx\EnumValueDefinition::class, $enumValue);

            self::assertSame($expEnumValue['value'], $enumValue->value);
            self::assertSame($expEnumValue['description'] ?? null, $enumValue->description);
            self::assertSame($expEnumValue['deprecationReason'] ?? null, $enumValue->deprecationReason);
        }
    }

    public static function getData(): iterable
    {
        yield 'simple EnumType with all' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'foo description',
                'values' => [
                    'yes' => [
                        'value' => true,
                        'description' => 'yes description',
                        'deprecationReason' => 'yes deprecation reason',
                    ],
                    'no' => [
                        'value' => false,
                    ],
                ],
            ],
            'config' => [
                'name' => 'foo',
                'description' => 'foo description',
                'values' => [
                    [
                        'name' => 'yes',
                        'value' => true,
                        'description' => 'yes description',
                        'deprecationReason' => 'yes deprecation reason',
                    ],
                    [
                        'name' => 'no',
                        'value' => false,
                    ],
                ],
            ],
        ];

        $obj1 = new \stdClass();
        $obj1->foo = 'bar';

        yield 'EnumType with all values configuration' => [
            'expected' => [
                'name' => 'foo',
                'values' => [
                    'bar' => [
                        'value' => 'bar value',
                        'description' => 'bar description',
                        'deprecationReason' => 'bar deprecation reason',
                    ],
                    'qwe' => [
                        'value' => 'qwe',
                    ],
                    'flag' => [
                        'value' => true,
                    ],
                    'asd' => [
                        'value' => 'asd',
                    ],
                    'zxc' => [
                        'value' => $obj1,
                    ],
                    'wer' => [
                        'value' => 'wer',
                    ],
                ],
            ],
            'config' => [
                'name' => 'foo',
                'values' => [
                    new EnumValue('bar', 'bar value', 'bar description', 'bar deprecation reason'),
                    'qwe',
                    'flag' => true,
                    [
                        'name' => 'asd',
                    ],
                    'ignored_name' => [
                        'name' => 'zxc',
                        'value' => $obj1,
                    ],
                    'wer' => [],
                ],
            ],
        ];

        yield 'raise exception when value name if not defined' => [
            'expected' => [
                'name' => 'foo',
            ],
            'config' => [
                'name' => 'foo',
                'values' => [
                    ['value' => 'without name'],
                ],
            ],
            'exception' => CantResolveEnumTypeException::class,
        ];

        yield 'raise exception when value has wrong config' => [
            'expected' => [
                'name' => 'foo',
            ],
            'config' => [
                'name' => 'foo',
                'values' => [
                    new \stdClass(),
                ],
            ],
            'exception' => CantResolveEnumTypeException::class,
        ];
    }

    private function getEnumType(array $config): AbstractEnumType
    {
        return new class($config) extends AbstractEnumType {
            public function __construct(array $config)
            {
                if (isset($config['name'])) {
                    $this->name = $config['name'];
                }

                if (isset($config['description'])) {
                    $this->description = $config['description'];
                }

                if (isset($config['values'])) {
                    $this->values = $config['values'];
                }
            }
        };
    }
}

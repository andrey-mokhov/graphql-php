<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\TypeResolver\Middleware\EnumTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\Tests\GraphQL\Fixture\AnnotatedEnum;
use Andi\Tests\GraphQL\Fixture\FooEnum;
use Andi\Tests\GraphQL\Fixture\PriorityEnum;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Internal\NativeAttributeReader;

#[CoversClass(EnumTypeMiddleware::class)]
#[UsesClass(AbstractDefinition::class)]
class EnumTypeMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private EnumTypeMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new EnumTypeMiddleware(new NativeAttributeReader());
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(MiddlewareInterface::class, $this->middleware);
    }

    public function testCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\EnumType(['name' => 'foo']));

        $this->middleware->process(null, $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, \ReflectionEnum $reflection): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();

        /** @var Webonyx\EnumType $enum */
        $enum = $this->middleware->process($reflection, $nextResolver);

        self::assertSame($expected['name'], $enum->name());
        self::assertSame($expected['description'] ?? null, $enum->description());

        foreach ($expected['values'] as $name => $case) {
            $enumValue = $enum->getValue($name);
            self::assertSame($case['value'], $enumValue->value);
            self::assertSame($case['description'] ?? null, $enumValue->description);
            self::assertSame($case['deprecationReason'] ?? null, $enumValue->deprecationReason);
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'FooEnum' => [
            'expected' => [
                'name' => 'FooEnum',
                'values' => [
                    'foo' => [
                        'value' => FooEnum::foo,
                    ],
                    'bar' => [
                        'value' => FooEnum::bar,
                    ],
                ],
            ],
            'reflection' => new \ReflectionEnum(FooEnum::class),
        ];

        yield 'AnnotatedEnum' => [
            'expected' => [
                'name' => 'AnnotatedEnum',
                'description' => 'AnnotatedEnum description.',
                'values' => [
                    'foo' => [
                        'value' => AnnotatedEnum::foo,
                        'description' => 'Foo case description.',
                        'deprecationReason' => 'Foo case is deprecated.',
                    ],
                ],
            ],
            'reflection' => new \ReflectionEnum(AnnotatedEnum::class),
        ];

        yield 'PriorityEnum' => [
            'expected' => [
                'name' => 'HiPriorityEnum',
                'description' => 'Hi priority description',
                'values' => [
                    'foo' => [
                        'value' => PriorityEnum::foo,
                    ],
                ],
            ],
            'reflection' => new \ReflectionEnum(PriorityEnum::class),
        ];
    }
}

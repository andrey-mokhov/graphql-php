<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentMiddleware;
use Andi\GraphQL\ArgumentResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\ArgumentInterface;
use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use Andi\GraphQL\Definition\Field\DeprecationReasonAwareInterface;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArgumentMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(LazyType::class)]
final class ArgumentMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $middleware = new ArgumentMiddleware(new TypeRegistry());

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcessCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(ArgumentResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn([]);

        $middleware = new ArgumentMiddleware(new TypeRegistry());
        $middleware->process(new \stdClass(), $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, ArgumentInterface $argument): void
    {
        $nextResolver = \Mockery::mock(ArgumentResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never()->andReturn([]);

        $middleware = new ArgumentMiddleware(new TypeRegistry());
        $config = $middleware->process($argument, $nextResolver);

        if (isset($config['type']) && \is_callable($config['type'])) {
            $config['type'] = \call_user_func($config['type']);
        }
        if ($config['type'] instanceof Webonyx\WrappingType) {
            $config['type'] = $config['type']->getWrappedType();
        }

        foreach ($expected as $name => $value) {
            self::assertArrayHasKey($name, $config);
            self::assertSame($value, $config[$name]);
        }

        if (! \array_key_exists('defaultValue', $expected)) {
            self::assertArrayNotHasKey('defaultValue', $config);
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'foo' => [
            'expected' => [
                'name' => 'foo',
                'description' => null,
                'type' => Webonyx\Type::string(),
            ],
            'argument' => new class implements ArgumentInterface {
                public function hasDefaultValue(): bool {
                    return false;
                }

                public function getName(): string
                {
                    return 'foo';
                }

                public function getDescription(): ?string
                {
                    return null;
                }

                public function getType(): string
                {
                    return Webonyx\StringType::class;
                }

                public function getMode(): int
                {
                    return 0;
                }
            },
        ];

        yield 'foo-with-description' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'foo description',
                'type' => Webonyx\Type::string(),
            ],
            'argument' => new class implements ArgumentInterface {
                public function hasDefaultValue(): bool {
                    return false;
                }

                public function getName(): string
                {
                    return 'foo';
                }

                public function getDescription(): ?string
                {
                    return 'foo description';
                }

                public function getType(): string
                {
                    return Webonyx\StringType::class;
                }

                public function getMode(): int
                {
                    return 0;
                }
            },
        ];

        yield 'foo-with-deprecation-reason' => [
            'expected' => [
                'deprecationReason' => 'deprecated reason for argument',
            ],
            'argument' => new class implements ArgumentInterface, DeprecationReasonAwareInterface {
                public function hasDefaultValue(): bool {
                    return false;
                }

                public function getName(): string
                {
                    return 'foo';
                }

                public function getDescription(): ?string
                {
                    return null;
                }

                public function getType(): string
                {
                    return Webonyx\StringType::class;
                }

                public function getMode(): int
                {
                    return 0;
                }

                public function getDeprecationReason(): ?string
                {
                    return 'deprecated reason for argument';
                }
            },
        ];

        yield 'foo-with-default-value' => [
            'expected' => [
                'defaultValue' => null,
            ],
            'argument' => new class implements ArgumentInterface, DefaultValueAwareInterface {
                public function hasDefaultValue(): bool {
                    return true;
                }

                public function getName(): string
                {
                    return 'foo';
                }

                public function getDescription(): ?string
                {
                    return null;
                }

                public function getType(): string
                {
                    return Webonyx\StringType::class;
                }

                public function getMode(): int
                {
                    return 0;
                }

                public function getDefaultValue(): mixed
                {
                    return null;
                }
            },
        ];

        yield 'full-definition-foo' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'foo description',
                'type' => Webonyx\Type::int(),
                'defaultValue' => 123,
                'deprecationReason' => 'deprecated reason for argument',
            ],
            'argument' => new class implements
                ArgumentInterface,
                DeprecationReasonAwareInterface,
                DefaultValueAwareInterface {
                public function hasDefaultValue(): bool {
                    return true;
                }

                public function getName(): string
                {
                    return 'foo';
                }

                public function getDescription(): ?string
                {
                    return 'foo description';
                }

                public function getType(): string
                {
                    return Webonyx\IntType::class;
                }

                public function getMode(): int
                {
                    return 0;
                }

                public function getDefaultValue(): mixed
                {
                    return 123;
                }

                public function getDeprecationReason(): ?string
                {
                    return 'deprecated reason for argument';
                }
            },
        ];
    }
}

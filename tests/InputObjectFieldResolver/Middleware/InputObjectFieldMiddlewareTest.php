<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use Andi\GraphQL\Definition\Field\DeprecationReasonAwareInterface;
use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\InputObjectFieldMiddleware;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputObjectFieldMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(LazyType::class)]
final class InputObjectFieldMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $middleware = new InputObjectFieldMiddleware(new TypeRegistry());

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcessCallNextResolver(): void
    {
        $middleware = new InputObjectFieldMiddleware(new TypeRegistry());

        $nextResolver = \Mockery::mock(InputObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')
            ->once()
            ->andReturn(new Webonyx\InputObjectField(['name' => 'foo', 'type' => Webonyx\Type::int()]));

        $middleware->process(null, $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, InputObjectFieldInterface $field, string $exception = null): void
    {
        $middleware = new InputObjectFieldMiddleware(new TypeRegistry());

        $nextResolver = \Mockery::mock(InputObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never()->andReturn(null);

        $inputObjectField = $middleware->process($field, $nextResolver);

        self::assertInstanceOf(Webonyx\InputObjectField::class, $inputObjectField);

        foreach ($expected as $property => $value) {
            switch ($property) {
                case 'type':
                    $type = $inputObjectField->getType();
                    if ($type instanceof Webonyx\WrappingType) {
                        $type = $type->getWrappedType();
                    }

                    self::assertSame($value, $type);
                    break;

                case 'defaultValue':
                    self::assertTrue($inputObjectField->defaultValueExists());
                    self::assertSame($value, $inputObjectField->defaultValue);
                    break;

                default:
                    self::assertSame($value, $inputObjectField->$property);
            }
        }

        if (! array_key_exists('defaultValue', $expected)) {
            self::assertFalse($inputObjectField->defaultValueExists());
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'foo' => [
            'expected' => [
                'name' => 'foo',
                'description' => null,
                'deprecationReason' => null,
                'type' => Webonyx\Type::int(),
            ],
            'field' => new class implements InputObjectFieldInterface, DeprecationReasonAwareInterface {
                public function getName(): string
                {
                    return 'foo';
                }

                public function getDescription(): ?string
                {
                    return null;
                }

                public function getDeprecationReason(): ?string
                {
                    return null;
                }

                public function hasDefaultValue(): bool
                {
                    return false;
                }

                public function getType(): string
                {
                    return Webonyx\IntType::class;
                }

                public function getMode(): int
                {
                    return 0;
                }
            },
        ];

        yield 'foo-filled' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'foo description',
                'deprecationReason' => 'foo deprecation reason',
                'type' => Webonyx\Type::int(),
                'defaultValue' => 123,
            ],
            'field' => new class implements
                InputObjectFieldInterface,
                DeprecationReasonAwareInterface,
                DefaultValueAwareInterface
            {
                public function getName(): string
                {
                    return 'foo';
                }

                public function getDescription(): ?string
                {
                    return 'foo description';
                }

                public function getDeprecationReason(): ?string
                {
                    return 'foo deprecation reason';
                }

                public function hasDefaultValue(): bool
                {
                    return true;
                }

                public function getType(): string
                {
                    return Webonyx\IntType::class;
                }

                public function getMode(): int
                {
                    return 0;
                }

                public function getDefaultValue(): int
                {
                    return 123;
                }
            },
        ];
    }
}

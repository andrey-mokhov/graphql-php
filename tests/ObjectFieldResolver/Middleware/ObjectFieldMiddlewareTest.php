<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\ArgumentResolver\Middleware\Next;
use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ComplexityAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectFieldMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(LazyType::class)]
#[UsesClass(ArgumentConfigurationMiddleware::class)]
#[UsesClass(Next::class)]
final class ObjectFieldMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $middleware = new ObjectFieldMiddleware(new TypeRegistry(), new ArgumentResolver());

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $middleware = new ObjectFieldMiddleware(new TypeRegistry(), new ArgumentResolver());
        $middleware->process(null, $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, ObjectFieldInterface $field): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();

        $info = \Mockery::mock(Webonyx\ResolveInfo::class);

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ArgumentConfigurationMiddleware());

        $middleware = new ObjectFieldMiddleware(new TypeRegistry(), $argumentResolver);

        $objectField = $middleware->process($field, $nextResolver);

        self::assertInstanceOf(Webonyx\FieldDefinition::class, $objectField);

        self::assertSame($expected['name'], $objectField->getName());
        self::assertSame($expected['description'] ?? null, $objectField->description);
        self::assertSame($expected['deprecationReason'] ?? null, $objectField->deprecationReason);

        $type = $objectField->getType();
        if ($type instanceof Webonyx\WrappingType) {
            $type = $type->getWrappedType();
        }
        self::assertSame($expected['type'], $type);

        if (isset($expected['arguments'])) {
            foreach ($expected['arguments'] as $name => $type) {
                $argument = $objectField->getArg($name);

                $argumentType = $argument->getType();
                if ($argumentType instanceof Webonyx\WrappingType) {
                    $argumentType = $argumentType->getWrappedType();
                }

                self::assertSame($type, $argumentType);
            }
        }

        if (isset($expected['resolve'])) {
            self::assertIsCallable($objectField->resolveFn);
            self::assertSame($expected['resolve'], call_user_func($objectField->resolveFn, null, [], null, $info));
        }

        if (isset($expected['complexity'])) {
            self::assertIsCallable($objectField->complexityFn);
            self::assertSame($expected['complexity'], call_user_func($objectField->complexityFn, 0, []));
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'foo' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'foo description',
                'deprecationReason' => 'foo is deprecated',
                'type' => Webonyx\Type::string(),
            ],
            'field' => new class implements ObjectFieldInterface {
                public function getName(): string {
                    return 'foo';
                }

                public function getDescription(): ?string
                {
                    return 'foo description';
                }

                public function getDeprecationReason(): ?string
                {
                    return 'foo is deprecated';
                }

                public function getType(): string
                {
                    return Webonyx\StringType::class;
                }

                public function getTypeMode(): int
                {
                    return 0;
                }
            },
        ];

        yield 'bar-full' => [
            'expected' => [
                'name' => 'bar',
                'description' => 'bar description',
                'deprecationReason' => 'bar is deprecated',
                'type' => Webonyx\Type::int(),
                'resolve' => 12,
                'complexity' => 15,
                'arguments' => [
                    'str'  => Webonyx\Type::string(),
                    'flag' => Webonyx\Type::boolean(),
                ],
            ],
            'field' => new class implements
                ObjectFieldInterface,
                ArgumentsAwareInterface,
                ResolveAwareInterface,
                ComplexityAwareInterface
            {
                public function getName(): string {
                    return 'bar';
                }

                public function getDescription(): ?string
                {
                    return 'bar description';
                }

                public function getDeprecationReason(): ?string
                {
                    return 'bar is deprecated';
                }

                public function getType(): string
                {
                    return Webonyx\IntType::class;
                }

                public function getTypeMode(): int
                {
                    return 0;
                }

                public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
                {
                    return 12;
                }

                public function complexity(int $childrenComplexity, array $args): int
                {
                    return 15;
                }

                public function getArguments(): iterable
                {
                    yield [
                        'name' => 'str',
                        'type' => Webonyx\Type::string(),
                    ];

                    yield [
                        'name' => 'flag',
                        'type' => Webonyx\Type::boolean(),
                    ];
                }
            },
        ];
    }
}

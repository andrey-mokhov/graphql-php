<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\Common\LazyObjectFields;
use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\ObjectFieldResolver\Middleware\Next;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\Middleware\GraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\Tests\GraphQL\Fixture;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;

#[CoversClass(GraphQLTypeMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(ObjectFieldMiddleware::class)]
#[UsesClass(ObjectFieldResolver::class)]
#[UsesClass(LazyObjectFields::class)]
#[UsesClass(LazyType::class)]
#[UsesClass(Next::class)]
final class GraphQLTypeMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TypeRegistryInterface $typeRegistry;

    private ContainerInterface $container;

    private GraphQLTypeMiddleware $middleware;

    protected function setUp(): void
    {
        $this->typeRegistry = new TypeRegistry();
        $this->container = new Container();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ArgumentConfigurationMiddleware());

        $objectFieldResolver = new ObjectFieldResolver();
        $objectFieldResolver->pipe(new ObjectFieldMiddleware($this->typeRegistry, $argumentResolver));

        $this->container->bind(ObjectFieldResolverInterface::class, $objectFieldResolver);
        $this->container->bind(TypeRegistryInterface::class, $this->typeRegistry);
        $this->container->bind(TypeRegistry::class, $this->typeRegistry);

        $this->middleware = new GraphQLTypeMiddleware($this->container);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(MiddlewareInterface::class, $this->middleware);
    }

    public function testCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(Webonyx\Type::id());

        $this->middleware->process(null, $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, string $class, string $exception = null): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();

        /** @var Webonyx\ObjectType $type */
        $type = $this->middleware->process($class, $nextResolver);

        self::assertInstanceOf($expected['instanceOf'], $type);
        self::assertSame($expected['name'], $type->name);
        self::assertSame($expected['description'] ?? null, $type->description);

        if (isset($expected['fields'])) {
            foreach ($expected['fields'] as $name => $expectedFieldType) {
                $field = $type->getField($name);
                $fieldType = $field->getType();
                if ($fieldType instanceof Webonyx\WrappingType) {
                    $fieldType = $fieldType->getWrappedType();
                }

                self::assertSame($expectedFieldType, $fieldType);
            }
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'foo' => [
            'expected' => [
                'instanceOf' => Webonyx\ObjectType::class,
                'name' => 'foo',
                'description' => 'foo description',
                'fields' => [
                    'field' => Webonyx\Type::id(),
                ],
            ],
            'class' => Fixture\ObjectType::class,
        ];
    }
}

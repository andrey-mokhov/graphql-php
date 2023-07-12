<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\ReflectionMethodMiddleware;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\ReflectionPropertyMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AbstractFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AbstractObjectFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\Middleware\AttributedGraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\GraphQL\WebonyxType\ObjectType;
use Andi\Tests\GraphQL\Fixture\SimpleObjectType;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Attributes\Internal\NativeAttributeReader;
use Spiral\Core\Container;

#[CoversClass(AttributedGraphQLTypeMiddleware::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(ReflectionParameterMiddleware::class)]
#[UsesClass(InputObjectFieldResolver::class)]
#[UsesClass(ReflectionMethodMiddleware::class)]
#[UsesClass(ReflectionPropertyMiddleware::class)]
#[UsesClass(AbstractFieldByReflectionMethodMiddleware::class)]
#[UsesClass(AbstractObjectFieldByReflectionMethodMiddleware::class)]
#[UsesClass(ObjectFieldResolver::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(AbstractDefinition::class)]
#[UsesClass(AbstractField::class)]
#[UsesClass(ObjectType::class)]
class AttributedGraphQLTypeMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ContainerInterface $container;

    private AttributedGraphQLTypeMiddleware $middleware;

    private TypeRegistryInterface $typeRegistry;

    protected function setUp(): void
    {
        $reader = new NativeAttributeReader();
        $this->typeRegistry = new TypeRegistry();
        $this->container = new Container();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ReflectionParameterMiddleware($reader, $this->typeRegistry));

        $objectFieldResolver = new ObjectFieldResolver();
        $objectFieldResolver->pipe(new ObjectFieldByReflectionMethodMiddleware(
            $reader,
            $this->typeRegistry,
            $argumentResolver,
            $this->container,
        ));

        $inputObjectFieldResolver = new InputObjectFieldResolver();
        $inputObjectFieldResolver->pipe(new ReflectionMethodMiddleware($reader, $this->typeRegistry));
        $inputObjectFieldResolver->pipe(new ReflectionPropertyMiddleware($reader, $this->typeRegistry));

        $this->middleware = new AttributedGraphQLTypeMiddleware(
            $this->container,
            $reader,
            $this->typeRegistry,
            $objectFieldResolver,
            $inputObjectFieldResolver,
            $this->container,
        );
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(MiddlewareInterface::class, $this->middleware);
    }

    public function testCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(\Mockery::mock(Webonyx\Type::class));

        $this->middleware->process(null, $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, \ReflectionClass $class): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();

        /** @var Webonyx\ObjectType $type */
        $type = $this->middleware->process($class, $nextResolver);

        self::assertInstanceOf($expected['instanceOf'], $type);
    }

    public static function getDataForProcess(): iterable
    {
        yield 'SimpleObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\ObjectType::class,
            ],
            'class' => new \ReflectionClass(SimpleObjectType::class),
        ];
    }
}

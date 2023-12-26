<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\Common\LazyInputObjectFields;
use Andi\GraphQL\Common\LazyObjectFields;
use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Common\LazyTypeIterator;
use Andi\GraphQL\Common\LazyTypeResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware as InputObjectFieldResolverMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\Next;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\Middleware\GraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\GraphQL\WebonyxType\DynamicObjectType;
use Andi\Tests\GraphQL\Fixture\Native;
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
#[UsesClass(LazyTypeIterator::class)]
#[UsesClass(Next::class)]
#[UsesClass(DynamicObjectType::class)]
#[UsesClass(LazyInputObjectFields::class)]
#[UsesClass(InputObjectFieldResolver::class)]
#[UsesClass(InputObjectFieldResolverMiddleware\InputObjectFieldMiddleware::class)]
#[UsesClass(InputObjectFieldResolverMiddleware\Next::class)]
#[UsesClass(LazyTypeResolver::class)]
final class GraphQLTypeMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TypeRegistryInterface $typeRegistry;

    private ContainerInterface $container;

    private GraphQLTypeMiddleware $middleware;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->typeRegistry = new TypeRegistry();
        $this->typeRegistry->register(new Webonyx\InterfaceType([
            'name' => 'FooInterface',
            'fields' => [
                'foo' => Webonyx\Type::string(),
            ],
        ]));
        $this->typeRegistry->register(new Webonyx\ObjectType([
            'name' => 'FooObjectType',
            'fields' => [
                'foo' => Webonyx\Type::string(),
            ],
        ]));

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ArgumentConfigurationMiddleware());

        $objectFieldResolver = new ObjectFieldResolver();
        $objectFieldResolver->pipe(new ObjectFieldMiddleware($this->typeRegistry, $argumentResolver));

        $inputObjectFieldResolver = new InputObjectFieldResolver();
        $inputObjectFieldResolver->pipe(new InputObjectFieldResolverMiddleware\InputObjectFieldMiddleware($this->typeRegistry));

        $this->container->bind(ObjectFieldResolverInterface::class, $objectFieldResolver);
        $this->container->bind(TypeRegistryInterface::class, $this->typeRegistry);
        $this->container->bind(InputObjectFieldResolverInterface::class, $inputObjectFieldResolver);

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

    public function testCallNextResolverWhenNotImplementedObject(): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(Webonyx\Type::id());

        $this->middleware->process(Native\UnknownType::class, $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, string $class, string $exception = null): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();
        $info = \Mockery::mock(Webonyx\ResolveInfo::class);

        /** @var Webonyx\ObjectType|Webonyx\InputObjectType|Webonyx\InterfaceType|Webonyx\UnionType|Webonyx\EnumType $type */
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

        if (isset($expected['interfaces'])) {
            $interfaces = $type->getInterfaces();
            foreach ($expected['interfaces'] as $interface) {
                self::assertContains($this->typeRegistry->get($interface), $interfaces);
            }
        }

        if (isset($expected['isTypeOf'])) {
            self::assertSame($expected['isTypeOf'], $type->isTypeOf(null, null, $info));
        }

        if (isset($expected['resolveField'])) {
            self::assertIsCallable($type->resolveFieldFn);
            self::assertSame($expected['resolveField'], \call_user_func($type->resolveFieldFn, null, [], null, $info));
        }

        if ($type instanceof DynamicObjectTypeInterface) {
            self::assertSame($type, $type->addAdditionalField(null));
        }

        // InputObjectType
        if (isset($expected['parseValue'])) {
            self::assertSame($expected['parseValue'], $type->parseValue([]));
        }

        if (isset($expected['resolveType'])) {
            $resolvedType = $this->typeRegistry->get($expected['resolveType']);
            self::assertSame($resolvedType, $type->resolveType(null, $this->typeRegistry, $info));
        }

        // UnionType
        if (isset($expected['types'])) {
            $types = $type->getTypes();
            foreach ($expected['types'] as $name) {
                self::assertContains($this->typeRegistry->get($name), $types);
            }
        }

        // EnumType
        if (isset($expected['values'])) {
            foreach ($expected['values'] as $name => $value) {
                self::assertSame($value, $type->getValue($name)?->value);
            }
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'ObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\ObjectType::class,
                'name' => 'ObjectType',
                'description' => 'ObjectType description',
                'fields' => [
                    'field' => Webonyx\Type::id(),
                ],
                'interfaces' => ['FooInterface'],
                'isTypeOf' => false,
                'resolveField' => 'object-type',
            ],
            'class' => Native\ObjectType::class,
        ];

        yield 'DynamicObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\ObjectType::class,
                'name' => 'DynamicObjectType',
                'description' => 'DynamicObjectType description',
                'fields' => [
                    'field' => Webonyx\Type::id(),
                ],
                'interfaces' => ['FooInterface'],
                'isTypeOf' => false,
                'resolveField' => 'object-type',
            ],
            'class' => Native\DynamicObjectType::class,
        ];

        yield 'InputObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\InputObjectType::class,
                'name' => 'InputObjectType',
                'description' => 'InputObjectType description',
                'fields' => [
                    'field' => Webonyx\Type::id(),
                ],
                'parseValue' => 'parsed',
            ],
            'class' => Native\InputObjectType::class,
        ];

        yield 'InterfaceType' => [
            'expected' => [
                'instanceOf' => Webonyx\InterfaceType::class,
                'name' => 'InterfaceType',
                'description' => 'InterfaceType description',
                'fields' => [
                    'field' => Webonyx\Type::id(),
                ],
                'resolveType' => 'FooObjectType',
            ],
            'class' => Native\InterfaceType::class,
        ];

        yield 'UnionType' => [
            'expected' => [
                'instanceOf' => Webonyx\UnionType::class,
                'name' => 'UnionType',
                'description' => 'UnionType description',
                'types' => ['FooObjectType'],
                'resolveType' => 'FooObjectType',
            ],
            'class' => Native\UnionType::class,
        ];

        yield 'EnumType' => [
            'expected' => [
                'instanceOf' => Webonyx\EnumType::class,
                'name' => 'EnumType',
                'description' => 'EnumType description',
                'values' => [
                    'name' => 'value',
                ],
            ],
            'class' => Native\EnumType::class,
        ];

        yield 'ScalarType' => [
            'expected' => [
                'instanceOf' => Webonyx\ScalarType::class,
                'name' => 'ScalarType',
                'description' => 'ScalarType description',
            ],
            'class' => Native\ScalarType::class,
        ];
    }
}

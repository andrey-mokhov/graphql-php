<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\Attribute;
use Andi\GraphQL\Common\DefinitionAwareTrait;
use Andi\GraphQL\Common\InputObjectFactory;
use Andi\GraphQL\Common\LazyInputObjectFields;
use Andi\GraphQL\Common\LazyObjectFields;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Common\LazyTypeByReflectionParameter;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Common\LazyTypeIterator;
use Andi\GraphQL\Common\LazyTypeResolver;
use Andi\GraphQL\Common\ResolveType;
use Andi\GraphQL\Field\ObjectField;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\Middleware as InputObjectFieldResolverMiddleware;
use Andi\GraphQL\ObjectFieldResolver\CantResolveObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\Middleware as ObjectFieldResolverMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\Middleware\AttributedGraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\GraphQL\WebonyxType\InputObjectType;
use Andi\GraphQL\WebonyxType\InterfaceType;
use Andi\GraphQL\WebonyxType\ObjectType;
use Andi\Tests\GraphQL\Fixture\Native\EnumType;
use Andi\Tests\GraphQL\Fixture\PriorityInputObjectType;
use Andi\Tests\GraphQL\Fixture\PriorityInterfaceType;
use Andi\Tests\GraphQL\Fixture\PriorityObjectType;
use Andi\Tests\GraphQL\Fixture\Simple2InputObjectType;
use Andi\Tests\GraphQL\Fixture\SimpleInputObjectType;
use Andi\Tests\GraphQL\Fixture\SimpleInterfaceType;
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
#[CoversClass(DefinitionAwareTrait::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(ReflectionParameterMiddleware::class)]
#[UsesClass(InputObjectFieldResolver::class)]
#[UsesClass(InputObjectFieldResolverMiddleware\ReflectionMethodMiddleware::class)]
#[UsesClass(InputObjectFieldResolverMiddleware\ReflectionPropertyMiddleware::class)]
#[UsesClass(InputObjectFieldResolverMiddleware\WebonyxInputObjectFieldMiddleware::class)]
#[UsesClass(InputObjectFieldResolverMiddleware\Next::class)]
#[UsesClass(ObjectFieldResolver::class)]
#[UsesClass(ObjectFieldResolverMiddleware\AbstractFieldByReflectionMethodMiddleware::class)]
#[UsesClass(ObjectFieldResolverMiddleware\AbstractObjectFieldByReflectionMethodMiddleware::class)]
#[UsesClass(ObjectFieldResolverMiddleware\ObjectFieldByReflectionPropertyMiddleware::class)]
#[UsesClass(ObjectFieldResolverMiddleware\ObjectFieldMiddleware::class)]
#[UsesClass(ObjectFieldResolverMiddleware\Next::class)]
#[UsesClass(ObjectFieldResolverMiddleware\WebonyxObjectFieldMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(ObjectType::class)]
#[UsesClass(LazyTypeByReflectionType::class)]
#[UsesClass(LazyObjectFields::class)]
#[UsesClass(LazyType::class)]
#[UsesClass(LazyTypeIterator::class)]
#[UsesClass(LazyTypeResolver::class)]
#[UsesClass(LazyTypeByReflectionParameter::class)]
#[UsesClass(LazyInputObjectFields::class)]
#[UsesClass(LazyParserType::class)]
#[UsesClass(ObjectField::class)]
#[UsesClass(CantResolveObjectFieldResolver::class)]
#[UsesClass(ResolveType::class)]
#[UsesClass(InterfaceType::class)]
#[UsesClass(Attribute\AbstractDefinition::class)]
#[UsesClass(Attribute\AbstractField::class)]
#[UsesClass(Attribute\InterfaceType::class)]
#[UsesClass(Attribute\InputObjectType::class)]
#[UsesClass(Attribute\InputObjectField::class)]
#[UsesClass(InputObjectFactory::class)]
#[UsesClass(InputObjectType::class)]
final class AttributedGraphQLTypeMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ContainerInterface $container;

    private AttributedGraphQLTypeMiddleware $middleware;

    private TypeRegistryInterface $typeRegistry;

    protected function setUp(): void
    {
        $reader = new NativeAttributeReader();
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
        $this->container->bind(TypeRegistryInterface::class, $this->typeRegistry);
        $this->container->bind(ResolveType::class, new ResolveType($this->typeRegistry));

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ReflectionParameterMiddleware($reader, $this->typeRegistry));

        $objectFieldResolver = new ObjectFieldResolver();
        $objectFieldResolver->pipe(new ObjectFieldResolverMiddleware\WebonyxObjectFieldMiddleware());
        $objectFieldResolver->pipe(new ObjectFieldResolverMiddleware\ObjectFieldMiddleware(
            $this->typeRegistry,
            $argumentResolver,
        ));
        $objectFieldResolver->pipe(new ObjectFieldResolverMiddleware\ObjectFieldByReflectionMethodMiddleware(
            $reader,
            $this->typeRegistry,
            $argumentResolver,
            $this->container,
            $this->container,
        ));
        $objectFieldResolver->pipe(new ObjectFieldResolverMiddleware\ObjectFieldByReflectionPropertyMiddleware(
            $reader,
            $this->typeRegistry,
        ));
        $objectFieldResolver->pipe(new ObjectFieldResolverMiddleware\InterfaceFieldByReflectionMethodMiddleware(
            $reader,
            $this->typeRegistry,
            $argumentResolver,
            $this->container,
            $this->container,
        ));

        $inputObjectFieldResolver = new InputObjectFieldResolver();
        $inputObjectFieldResolver->pipe(
            new InputObjectFieldResolverMiddleware\ReflectionMethodMiddleware($reader, $this->typeRegistry),
        );
        $inputObjectFieldResolver->pipe(
            new InputObjectFieldResolverMiddleware\ReflectionPropertyMiddleware($reader, $this->typeRegistry),
        );
        $inputObjectFieldResolver->pipe(new InputObjectFieldResolverMiddleware\WebonyxInputObjectFieldMiddleware());

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

    public function testCallNextResolverWhenReflectionWithoutTargetAttribute(): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(\Mockery::mock(Webonyx\Type::class));

        $this->middleware->process(new \ReflectionClass(EnumType::class), $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, string|\ReflectionClass $class): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();
        $info = \Mockery::mock(Webonyx\ResolveInfo::class);

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

        if (isset($expected['interfaces'])) {
            $interfaces = $type->getInterfaces();
            foreach ($expected['interfaces'] as $interface) {
                self::assertTrue(in_array($this->typeRegistry->get($interface), $interfaces, true));
            }
        }

        if (isset($expected['isTypeOf'])) {
            self::assertSame($expected['isTypeOf'], $type->isTypeOf(null, null, $info));
        }

        if (isset($expected['resolveField'])) {
            self::assertIsCallable($type->resolveFieldFn);
            self::assertSame($expected['resolveField'], call_user_func($type->resolveFieldFn, null, [], null, $info));
        }

        if ($type instanceof DynamicObjectTypeInterface) {
            self::assertSame($type, $type->addAdditionalField(null));
        }

        // InputObjectType
        if (isset($expected['parseValue'])) {
            self::assertEquals($expected['parseValue'], $type->parseValue(['foo' => 'asd', 'bar' => 'qwe']));
        }

        // InterfaceType
        if (isset($expected['resolveType'])) {
            $resolvedType = $this->typeRegistry->get($expected['resolveType']);
            self::assertSame($resolvedType, $type->resolveType(null, $this->typeRegistry, $info));
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'SimpleObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\ObjectType::class,
                'name' => 'SimpleObjectType',
                'description' => 'ObjectType description.',
                'fields' => [
                    'foo' => Webonyx\Type::int(),
                    'bar' => Webonyx\Type::string(),
                ],
            ],
            'class' => new \ReflectionClass(SimpleObjectType::class),
        ];

        yield 'PriorityObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\ObjectType::class,
                'name' => 'HiPriorityObjectType',
                'description' => 'Hi priority description',
                'fields' => [
                    'foo' => Webonyx\Type::string(),
                ],
                'interfaces' => [
                    'FooInterface',
                ],
                'isTypeOf' => false,
                'resolveField' => 15,
            ],
            'class' => new \ReflectionClass(PriorityObjectType::class),
        ];

        yield 'SimpleInterfaceType' => [
            'expected' => [
                'instanceOf' => Webonyx\InterfaceType::class,
                'name' => 'SimpleInterfaceType',
                'description' => 'InterfaceType description.',
                'fields' => [
                    'foo' => Webonyx\Type::string(),
                ],
                'resolveType' => 'FooObjectType',
            ],
            'class' => new \ReflectionClass(SimpleInterfaceType::class),
        ];

        yield 'PriorityInterfaceType' => [
            'expected' => [
                'instanceOf' => Webonyx\InterfaceType::class,
                'name' => 'HiPriorityInterfaceType',
                'description' => 'Hi priority description',
                'fields' => [
                    'foo' => Webonyx\Type::int(),
                ],
                'resolveType' => 'FooObjectType',
            ],
            'class' => new \ReflectionClass(PriorityInterfaceType::class),
        ];

        yield 'SimpleInputObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\InputObjectType::class,
                'name' => 'SimpleInputObjectType',
                'description' => 'SimpleInputObjectType description.',
                'fields' => [
                    'foo' => Webonyx\Type::string(),
                    'bar' => Webonyx\Type::string(),
                ],
                'parseValue' => 17,
            ],
            'class' => new \ReflectionClass(SimpleInputObjectType::class),
        ];

        $obj = new Simple2InputObjectType();
        $obj->foo = 'asd';
        $obj->bar = 'qwe';

        yield 'Simple2InputObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\InputObjectType::class,
                'name' => 'Simple2InputObjectType',
                'fields' => [
                    'foo' => Webonyx\Type::string(),
                    'bar' => Webonyx\Type::string(),
                ],
                'parseValue' => $obj,
            ],
            'class' => Simple2InputObjectType::class,
        ];

        yield 'empty description' => [
            'expected' => [
                'instanceOf' => Webonyx\InputObjectType::class,
                'name' => 'foo',
            ],
            'class' => new \ReflectionClass(new #[Attribute\InputObjectType(name: 'foo')] class {}),
        ];

        yield 'PriorityInputObjectType' => [
            'expected' => [
                'instanceOf' => Webonyx\InputObjectType::class,
                'name' => 'HiPriorityInputObjectType',
                'description' => 'Hi priority description',
                'fields' => [
                    'foo' => Webonyx\Type::int(),
                    'bar' => Webonyx\Type::id(),
                ],
                'parseValue' => 12,
            ],
            'class' => new \ReflectionClass(PriorityInputObjectType::class),
        ];
    }
}

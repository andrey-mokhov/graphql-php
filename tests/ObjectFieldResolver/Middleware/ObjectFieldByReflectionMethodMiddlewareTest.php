<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\Next;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionParameter;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Common\ResolverArguments;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\Field;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AbstractFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AbstractObjectFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Internal\NativeAttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Container;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;

#[CoversClass(AbstractObjectFieldByReflectionMethodMiddleware::class)]
#[CoversClass(AbstractFieldByReflectionMethodMiddleware::class)]
#[CoversClass(Field\ObjectField::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(ReflectionParameterMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(AbstractDefinition::class)]
#[UsesClass(AbstractField::class)]
#[UsesClass(LazyParserType::class)]
#[UsesClass(LazyTypeByReflectionType::class)]
#[UsesClass(LazyTypeByReflectionParameter::class)]
#[UsesClass(ObjectField::class)]
#[UsesClass(Next::class)]
#[UsesClass(Argument::class)]
#[UsesClass(ResolverArguments::class)]
final class ObjectFieldByReflectionMethodMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ObjectFieldByReflectionMethodMiddleware $middleware;

    private TypeRegistryInterface $typeRegistry;

    private ReaderInterface $reader;

    protected function setUp(): void
    {
        $this->reader = new NativeAttributeReader();
        $this->typeRegistry = new TypeRegistry();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ReflectionParameterMiddleware($this->reader, $this->typeRegistry));

        $container = new Container();
        $this->middleware = new ObjectFieldByReflectionMethodMiddleware(
            $this->reader,
            $this->typeRegistry,
            $argumentResolver,
            $container,
            $container,
        );

    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(MiddlewareInterface::class, $this->middleware);
    }

    public function testCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $this->middleware->process(null, $nextResolver);
    }

    public function testCallNextResolverWhenMethodWithoutAttribute(): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $object = new class {
            public function foo() {}
        };

        $classReflection = new \ReflectionClass($object);
        $field = null;
        foreach ($classReflection->getMethods() as $field) {
            break;
        }

        $this->middleware->process($field, $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, object $object, string $exception = null): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();

        $field = null;
        $reflectionClass = new \ReflectionClass($object);
        foreach ($reflectionClass->getMethods() as $field) {
            break;
        }

        if (null !== $exception) {
            $this->expectException($exception);
        }

        $objectField = $this->middleware->process($field, $nextResolver);

        self::assertInstanceOf(Webonyx\FieldDefinition::class, $objectField);

        self::assertSame($expected['name'], $objectField->getName());
        self::assertSame($expected['description'] ?? null, $objectField->description);
        self::assertSame($expected['deprecationReason'] ?? null, $objectField->deprecationReason);

        self::assertSame($expected['type'], (string) $objectField->getType());

        if (isset($expected['arguments'])) {
            foreach ($expected['arguments'] as $name => $type) {
                $argument = $objectField->getArg($name);

                self::assertSame($type, (string) $argument->getType());
            }
        }

        if (isset($expected['resolve']) || array_key_exists('resolve', $expected)) {
            $args = [$object, ['str' => 'string value', 'flag' => false], null, \Mockery::mock(Webonyx\ResolveInfo::class)];
            self::assertSame($expected['resolve'], call_user_func_array($objectField->resolveFn, $args));
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'foo' => [
            'expected' => [
                'name' => 'foo',
                'type' => 'String!',
                'resolve' => 'qew',
            ],
            'object' => new class {
                #[ObjectField]
                public function foo(): string {
                    return 'qew';
                }
            },
        ];

        yield 'foo-without-attribute-data' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'Foo description.',
                'deprecationReason' => 'Foo is deprecated.',
                'type' => 'Int!',
                'arguments' => [
                    'str' => 'String!',
                    'flag' => 'Boolean!',
                ],
                'resolve' => 1,
            ],
            'object' => new class {
                /**
                 * Foo description.
                 *
                 * @return int
                 *
                 * @deprecated Foo is deprecated.
                 */
                #[ObjectField]
                public function getFoo(#[Argument] string $str, #[Argument] bool $flag): int {
                    return 1;
                }
            },
        ];

        yield 'bar-with-attribute' => [
            'expected' => [
                'name' => 'bar',
                'description' => 'Bar description',
                'deprecationReason' => 'reason',
                'type' => 'ID',
                'resolve' => 1,
            ],
            'object' => new class {
                #[ObjectField(name: 'bar', description: 'Bar description', type: 'ID', deprecationReason: 'reason')]
                public function getFoo(): int {
                    return 1;
                }
            },
        ];

        yield 'raise exception when field type undefined' => [
            'expected' => [],
            'object' => new class {
                #[ObjectField]
                public function foo() {}
            },
            'exception' => CantResolveGraphQLTypeException::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\Next;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionParameter;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\Field\OuterObjectField;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AbstractFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AbstractOuterObjectFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AdditionalFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
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
use Spiral\Core\InvokerInterface;

#[CoversClass(AbstractOuterObjectFieldByReflectionMethodMiddleware::class)]
#[CoversClass(AbstractFieldByReflectionMethodMiddleware::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(ReflectionParameterMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(AbstractDefinition::class)]
#[UsesClass(AbstractField::class)]
#[UsesClass(AdditionalField::class)]
#[UsesClass(LazyParserType::class)]
#[UsesClass(LazyTypeByReflectionType::class)]
#[UsesClass(LazyTypeByReflectionParameter::class)]
#[UsesClass(OuterObjectField::class)]
#[UsesClass(Next::class)]
#[UsesClass(Argument::class)]
final class AdditionalFieldByReflectionMethodMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private AdditionalFieldByReflectionMethodMiddleware $middleware;

    private TypeRegistryInterface $typeRegistry;

    private ReaderInterface $reader;

    private InvokerInterface $invoker;

    protected function setUp(): void
    {
        $this->reader = new NativeAttributeReader();
        $this->typeRegistry = new TypeRegistry();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ReflectionParameterMiddleware($this->reader, $this->typeRegistry));

        $this->middleware = new AdditionalFieldByReflectionMethodMiddleware(
            $this->reader,
            $this->typeRegistry,
            $argumentResolver,
            $this->invoker = \Mockery::mock(InvokerInterface::class),
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
    }

    public static function getDataForProcess(): iterable
    {
        yield 'foo' => [
            'expected' => [
                'name' => 'foo',
                'type' => Webonyx\Type::string(),
            ],
            'object' => new class {
                #[AdditionalField(targetType: 'Query')]
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
                'type' => Webonyx\Type::int(),
                'arguments' => [
                    'str' => Webonyx\Type::string(),
                    'flag' => Webonyx\Type::boolean(),
                ],
            ],
            'object' => new class {
                /**
                 * Foo description.
                 *
                 * @return int
                 *
                 * @deprecated Foo is deprecated.
                 */
                #[AdditionalField(targetType: 'Query')]
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
                'type' => Webonyx\Type::id(),
            ],
            'object' => new class {
                #[AdditionalField(
                    targetType: 'Query',
                    name: 'bar',
                    description: 'Bar description',
                    type: 'ID',
                    deprecationReason: 'reason',
                )]
                public function getFoo(): int {
                    return 1;
                }
            },
        ];

        yield 'raise-exception' => [
            'expected' => [],
            'object' => new class {
                #[AdditionalField(targetType: 'Query')]
                public function foo() {}
            },
            'exception' => CantResolveGraphQLTypeException::class,
        ];
    }
}

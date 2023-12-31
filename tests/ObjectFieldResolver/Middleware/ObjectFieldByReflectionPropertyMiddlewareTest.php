<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldByReflectionPropertyMiddleware;
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

#[CoversClass(ObjectFieldByReflectionPropertyMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(AbstractDefinition::class)]
#[UsesClass(AbstractField::class)]
#[UsesClass(LazyParserType::class)]
#[UsesClass(LazyTypeByReflectionType::class)]
final class ObjectFieldByReflectionPropertyMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ObjectFieldByReflectionPropertyMiddleware $middleware;

    private TypeRegistryInterface $typeRegistry;

    private ReaderInterface $reader;

    protected function setUp(): void
    {
        $this->reader = new NativeAttributeReader();
        $this->typeRegistry = new TypeRegistry();

        $this->middleware = new ObjectFieldByReflectionPropertyMiddleware(
            $this->reader,
            $this->typeRegistry,
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

    #[DataProvider('getDataForProcess')]
    public function testProcess(array $expected, object $object, string $exception = null): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();

        $info = \Mockery::mock(Webonyx\ResolveInfo::class);

        $field = null;
        $reflectionClass = new \ReflectionClass($object);
        foreach ($reflectionClass->getProperties() as $field) {
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

        if (isset($expected['resolve'])) {
            self::assertIsCallable($objectField->resolveFn);
            self::assertSame($expected['resolve'], \call_user_func($objectField->resolveFn, $object, [], null, $info));
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'foo' => [
            'expected' => [
                'name' => 'foo',
                'type' => Webonyx\Type::boolean(),
                'resolve' => false,
            ],
            'object' => new class {
                public bool $foo = false;
            },
        ];

        yield 'foo-with-docblock' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'Foo description.',
                'deprecationReason' => 'Foo is deprecated.',
                'type' => Webonyx\Type::int(),
                'resolve' => 12,
            ],
            'object' => new class {
                /**
                 * Foo description.
                 *
                 * @var int
                 *
                 * @deprecated Foo is deprecated.
                 */
                public int $foo = 12;
            },
        ];

        yield 'foo in constructor with docblock' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'Foo description.',
                'type' => Webonyx\Type::int(),
                'resolve' => 12,
            ],
            'object' => new class {
                /**
                 * @param int $foo Foo description.
                 */
                public function __construct(
                    #[ObjectField]
                    public int $foo = 12,
                ) {
                }
            },
        ];

        yield 'bar-with-attribute' => [
            'expected' => [
                'name' => 'bar',
                'description' => 'bar description',
                'deprecationReason' => 'bar is deprecated',
                'type' => Webonyx\Type::id(),
                'resolve' => 'qwerty',
            ],
            'object' => new class {
                /**
                 * Foo description.
                 *
                 * @var string
                 *
                 * @deprecated Foo is deprecated.
                 */
                #[ObjectField(
                    name: 'bar',
                    description: 'bar description',
                    type: 'ID',
                    deprecationReason: 'bar is deprecated',
                )]
                private string $foo = 'qwerty';
            },
        ];

        yield 'raise-exception' => [
            'expected' => [],
            'object' => new class {
                private $foo;
            },
            'exception' => CantResolveGraphQLTypeException::class,
        ];
    }
}

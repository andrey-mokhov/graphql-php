<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\Exception\NotFoundException;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\ReflectionPropertyMiddleware;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\ReaderInterface;

#[CoversClass(ReflectionPropertyMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(LazyTypeByReflectionType::class)]
#[UsesClass(InputObjectField::class)]
#[UsesClass(LazyParserType::class)]
#[UsesClass(NotFoundException::class)]
final class ReflectionPropertyMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $middleware = new ReflectionPropertyMiddleware(\Mockery::mock(ReaderInterface::class), new TypeRegistry());

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcessCallNextResolver(): void
    {
        $middleware = new ReflectionPropertyMiddleware(\Mockery::mock(ReaderInterface::class), new TypeRegistry());

        $nextResolver = \Mockery::mock(InputObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')
            ->once()
            ->andReturn(new Webonyx\InputObjectField(['name' => 'foo', 'type' => Webonyx\Type::int()]));

        $middleware->process(null, $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(
        array $expected,
        object $object,
        ?InputObjectField $attribute,
        string $exception = null,
    ): void {
        $reader = \Mockery::mock(ReaderInterface::class);
        $reader->shouldReceive('firstPropertyMetadata')->once()->andReturn($attribute);
        $nextResolver = \Mockery::mock(InputObjectFieldResolverInterface::class);

        $middleware = new ReflectionPropertyMiddleware($reader, new TypeRegistry());

        $object = new \ReflectionClass($object);
        $field = null;
        foreach ($object->getProperties() as $field) {
            break;
        }

        if (null !== $exception) {
            $this->expectException($exception);
        }
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

        if (! \array_key_exists('defaultValue', $expected)) {
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
            'object' => new class {
                private int $foo;
            },
            'attribute' => null,
        ];

        yield 'foo in constructor' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'Foo description.',
                'deprecationReason' => null,
                'type' => Webonyx\Type::int(),
            ],
            'object' => new class {
                /**
                 * @param int $foo Foo description.
                 */
                public function __construct(
                    private int $foo = 12,
                ) {
                }
            },
            'attribute' => null,
        ];

        yield 'bar-with-attribute' => [
            'expected' => [
                'name' => 'bar',
                'description' => 'bar description',
                'deprecationReason' => 'deprecated property',
                'type' => Webonyx\Type::string(),
                'defaultValue' => 'qwe',
            ],
            'object' => new class {
                private int $foo;
            },
            'attribute' => new InputObjectField(
                name: 'bar',
                description: 'bar description',
                type: 'String',
                deprecationReason: 'deprecated property',
                defaultValue: 'qwe',
            ),
        ];

        yield 'foo-with-annotation' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'Property description.',
                'deprecationReason' => 'Deprecated property.',
                'type' => Webonyx\Type::int(),
                'defaultValue' => 123,
            ],
            'object' => new class {
                /**
                 * Property description.
                 *
                 * @var int
                 * @deprecated Deprecated property.
                 */
                private int $foo = 123;
            },
            'attribute' => null,
        ];

        yield 'bar-with-mixed-definition' => [
            'expected' => [
                'name' => 'bar',
                'description' => 'description from attribute',
                'deprecationReason' => 'Deprecated property.',
                'type' => Webonyx\Type::id(),
                'defaultValue' => 234,
            ],
            'object' => new class {
                /**
                 * Property description.
                 *
                 * @var int
                 * @deprecated Deprecated property.
                 */
                private int $foo = 123;
            },
            'attribute' => new InputObjectField(
                name: 'bar',
                description: 'description from attribute',
                type: 'ID',
                defaultValue: 234,
            ),
        ];

        yield 'raise-exception-property-without-type' => [
            'expected' => [],
            'object' => new class {
                private $foo;
            },
            'attribute' => null,
            'exception' => CantResolveGraphQLTypeException::class,
        ];

        yield 'raise-exception-property-with-unknown-type' => [
            'expected' => [
                'type' => 'raise exception',
                'defaultValue' => null,
            ],
            'object' => new class {
                private $foo;
            },
            'attribute' => new InputObjectField(type: 'UnknownType'),
            'exception' => NotFoundException::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\ArgumentResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionParameter;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\Exception\NotFoundException;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\ReaderInterface;

#[CoversClass(ReflectionParameterMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(LazyTypeByReflectionParameter::class)]
#[UsesClass(LazyTypeByReflectionType::class)]
#[UsesClass(Argument::class)]
#[UsesClass(LazyParserType::class)]
#[UsesClass(NotFoundException::class)]
final class ReflectionParameterMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $reader = \Mockery::mock(ReaderInterface::class);
        $middleware = new ReflectionParameterMiddleware($reader, new TypeRegistry());

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcessCallNextResolver(): void
    {
        $reader = \Mockery::mock(ReaderInterface::class);

        $nextResolver = \Mockery::mock(ArgumentResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn([]);

        $middleware = new ReflectionParameterMiddleware($reader, new TypeRegistry());
        $middleware->process(new \stdClass(), $nextResolver);
    }

    #[DataProvider('getDataForProcess')]
    public function testProcess(
        array $expected,
        \ReflectionClass $class,
        ?Argument $attribute,
        string $exception = null
    ): void {
        $reader = \Mockery::mock(ReaderInterface::class);
        $reader->shouldReceive('firstParameterMetadata')->once()->andReturn($attribute);

        $nextResolver = \Mockery::mock(ArgumentResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never()->andReturn([]);

        $parameter = null;
        foreach ($class->getMethods() as $method) {
            foreach ($method->getParameters() as $parameter) {
                break;
            }
        }

        self::assertInstanceOf(\ReflectionParameter::class, $parameter);

        $middleware = new ReflectionParameterMiddleware($reader, new TypeRegistry());

        if (null !== $exception) {
            $this->expectException($exception);
        }

        $config = $middleware->process($parameter, $nextResolver);
        if (isset($config['type']) && \is_callable($config['type'])) {
            $config['type'] = \call_user_func($config['type']);
        }
        if ($config['type'] instanceof Webonyx\WrappingType) {
            $config['type'] = $config['type']->getWrappedType();
        }

        foreach ($expected as $name => $value) {
            self::assertArrayHasKey($name, $config);
            self::assertSame($value, $config[$name]);
        }

        if (! \array_key_exists('defaultValue', $expected)) {
            self::assertArrayNotHasKey('defaultValue', $config);
        }
    }

    public static function getDataForProcess(): iterable
    {
        yield 'bar-parameter' => [
            'expected' => [
                'name' => 'bar',
                'description' => null,
                'deprecationReason' => null,
                'type' => Webonyx\Type::int(),
            ],
            'class' => new \ReflectionClass(new class {
                public function foo(int $bar) {}
            }),
            'attribute' => null,
        ];

        yield 'bar-parameter-with-attribute' => [
            'expected' => [
                'name' => 'qwe',
                'description' => 'qwe description',
                'type' => Webonyx\Type::id(),
                'deprecationReason' => 'deprecation reason',
                'defaultValue' => 123,
            ],
            'class' => new \ReflectionClass(new class {
                public function foo(int $bar) {}
            }),
            'attribute' => new Argument(
                name: 'qwe',
                description: 'qwe description',
                type: 'ID',
                deprecationReason: 'deprecation reason',
                defaultValue: 123,
            ),
        ];

        yield 'bar-parameter-with-annotation' => [
            'expected' => [
                'name' => 'bar',
                'description' => 'bar annotation',
                'type' => Webonyx\Type::int(),
                'defaultValue' => 234,
            ],
            'class' => new \ReflectionClass(new class {
                /**
                 * @param int $bar bar annotation
                 */
                public function foo(int $bar = 234) {}
            }),
            'attribute' => null,
        ];

        yield 'bar-parameter-with-psalm-annotation' => [
            'expected' => [
                'name' => 'bar',
                'description' => 'bar annotation',
                'type' => Webonyx\Type::int(),
                'defaultValue' => 234,
            ],
            'class' => new \ReflectionClass(new class {
                /**
                 * @psalm-param int $bar bar annotation
                 */
                public function foo(int $bar = 234) {}
            }),
            'attribute' => null,
        ];

        yield 'bar-parameter-with-mixed-annotation' => [
            'expected' => [
                'name' => 'asd',
                'description' => 'bar annotation',
                'type' => Webonyx\Type::id(),
                'defaultValue' => 890,
            ],
            'class' => new \ReflectionClass(new class {
                /**
                 * @param int $bar bar annotation
                 *
                 * @return void
                 */
                public function foo(int $bar = 234) {}
            }),
            'attribute' => new Argument(
                name: 'asd',
                type: 'ID',
                defaultValue: 890
            ),
        ];

        yield 'raise-exception-when-parameter-has-not-type' => [
            'expected' => [],
            'class' => new \ReflectionClass(new class {
                public function foo($bar) {}
            }),
            'attribute' => null,
            'exception' => CantResolveGraphQLTypeException::class,
        ];

        yield 'raise-exception-when-parameter-has-unregister-type' => [
            'expected' => [],
            'class' => new \ReflectionClass(new class {
                public function foo(mixed $bar) {}
            }),
            'attribute' => null,
            'exception' => CantResolveGraphQLTypeException::class,
        ];

        yield 'raise-exception-when-parameter-has-unregister-type-via-attribute' => [
            'expected' => [],
            'class' => new \ReflectionClass(new class {
                public function foo(int $bar) {}
            }),
            'attribute' => new Argument(type: 'UnknownType'),
            'exception' => NotFoundException::class,
        ];

        yield 'success-when-parameter-has-not-type' => [
            'expected' => [
                'name' => 'bar',
                'type' => Webonyx\Type::int(),
            ],
            'class' => new \ReflectionClass(new class {
                public function foo($bar) {}
            }),
            'attribute' => new Argument(type: 'Int'),
        ];
    }
}

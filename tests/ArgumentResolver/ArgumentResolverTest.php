<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ArgumentResolver;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\ArgumentResolver\CantResolveArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ArgumentResolver\Middleware\Next;
use Andi\GraphQL\ArgumentResolver\Middleware\PipelineInterface;
use Andi\GraphQL\Exception\CantResolveArgumentException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArgumentResolver::class)]
#[UsesClass(Next::class)]
#[UsesClass(CantResolveArgumentResolver::class)]
class ArgumentResolverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $resolver = new ArgumentResolver();

        self::assertInstanceOf(ArgumentResolverInterface::class, $resolver);
        self::assertInstanceOf(PipelineInterface::class, $resolver);
    }

    public function testEmptyPipeline(): void
    {
        $resolver = new ArgumentResolver();

        $this->expectException(CantResolveArgumentException::class);
        $resolver->resolve(null);
    }

    public function testPipeMiddleware(): void
    {
        $resolver = new ArgumentResolver();

        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->once()->andReturn([]);

        $resolver->pipe($middleware);
        $resolver->resolve(null);
    }

    public function testPriority(): void
    {
        $resolver = new ArgumentResolver();

        $middlewareHi = \Mockery::mock(MiddlewareInterface::class);
        $middlewareHi->shouldReceive('process')->once()->andReturn([]);

        $middlewareLow = \Mockery::mock(MiddlewareInterface::class);
        $middlewareLow->shouldReceive('process')->never()->andReturn([]);

        $resolver->pipe($middlewareLow, 1);
        $resolver->pipe($middlewareHi, 2);

        $resolver->resolve(null);
    }

    public function testCloning(): void
    {
        $resolver = new ArgumentResolver();

        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->andReturn(['foo' => 'bar']);

        $resolver->pipe($middleware);
        $clone = clone $resolver;

        self::assertSame(['foo' => 'bar'], $resolver->resolve(null));
        self::assertSame(['foo' => 'bar'], $clone->resolve(null));

        $middlewareHi = \Mockery::mock(MiddlewareInterface::class);
        $middlewareHi->shouldReceive('process')->andReturn(['baz' => 'qwe']);

        $clone->pipe($middlewareHi, 100); // hi priority

        self::assertSame(['foo' => 'bar'], $resolver->resolve(null));
        self::assertSame(['baz' => 'qwe'], $clone->resolve(null));
    }
}

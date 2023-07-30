<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\InputObjectFieldResolver;

use Andi\GraphQL\Exception\CantResolveInputObjectFieldException;
use Andi\GraphQL\InputObjectFieldResolver\CantResolveInputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\Next;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\PipelineInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputObjectFieldResolver::class)]
#[UsesClass(CantResolveInputObjectFieldResolver::class)]
#[UsesClass(Next::class)]
final class InputObjectFieldResolverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $resolver = new InputObjectFieldResolver();

        self::assertInstanceOf(InputObjectFieldResolverInterface::class, $resolver);
        self::assertInstanceOf(PipelineInterface::class, $resolver);
    }

    public function testEmptyPipeline(): void
    {
        $resolver = new InputObjectFieldResolver();

        $this->expectException(CantResolveInputObjectFieldException::class);
        $resolver->resolve(null);
    }

    public function testPipe(): void
    {
        $resolver = new InputObjectFieldResolver();

        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->once()->andReturn(new Webonyx\InputObjectField(['name' => 'foo']));

        $resolver->pipe($middleware);
        $resolver->resolve(null);
    }

    public function testPriority(): void
    {
        $resolver = new InputObjectFieldResolver();

        $middlewareHi = \Mockery::mock(MiddlewareInterface::class);
        $middlewareHi->shouldReceive('process')->once()->andReturn(new Webonyx\InputObjectField(['name' => 'foo']));

        $middlewareLow = \Mockery::mock(MiddlewareInterface::class);
        $middlewareLow->shouldReceive('process')->never()->andReturn(new Webonyx\InputObjectField(['name' => 'bar']));

        $resolver->pipe($middlewareLow, 1);
        $resolver->pipe($middlewareHi, 2);

        $resolver->resolve(null);

    }

    public function testCloning(): void
    {
        $resolver = new InputObjectFieldResolver();

        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->andReturn(new Webonyx\InputObjectField(['name' => 'foo']));

        $resolver->pipe($middleware);
        $clone = clone $resolver;

        self::assertSame('foo', $resolver->resolve(null)->name);
        self::assertSame('foo', $clone->resolve(null)->name);

        $middlewareHi = \Mockery::mock(MiddlewareInterface::class);
        $middlewareHi->shouldReceive('process')->andReturn(new Webonyx\InputObjectField(['name' => 'bar']));

        $clone->pipe($middlewareHi, 100); // hi priority

        self::assertSame('foo', $resolver->resolve(null)->name);
        self::assertSame('bar', $clone->resolve(null)->name);
    }
}

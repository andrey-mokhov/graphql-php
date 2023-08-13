<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver;

use Andi\GraphQL\Exception\CantResolveObjectFieldException;
use Andi\GraphQL\ObjectFieldResolver\CantResolveObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\Next;
use Andi\GraphQL\ObjectFieldResolver\Middleware\PipelineInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectFieldResolver::class)]
#[UsesClass(CantResolveObjectFieldResolver::class)]
#[UsesClass(Next::class)]
final class ObjectFieldResolverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $resolver = new ObjectFieldResolver();

        self::assertInstanceOf(ObjectFieldResolverInterface::class, $resolver);
        self::assertInstanceOf(PipelineInterface::class, $resolver);
    }

    public function testEmptyPipeline(): void
    {
        $resolver = new ObjectFieldResolver();

        $this->expectException(CantResolveObjectFieldException::class);
        $resolver->resolve(null);
    }

    public function testPipe(): void
    {
        $resolver = new ObjectFieldResolver();

        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $resolver->pipe($middleware);
        $resolver->resolve(null);
    }

    public function testPriority(): void
    {
        $resolver = new ObjectFieldResolver();

        $middlewareHi = \Mockery::mock(MiddlewareInterface::class);
        $middlewareHi->shouldReceive('process')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $middlewareLow = \Mockery::mock(MiddlewareInterface::class);
        $middlewareLow->shouldReceive('process')->never()->andReturn(new Webonyx\FieldDefinition(['name' => 'bar']));

        $resolver->pipe($middlewareLow, 1);
        $resolver->pipe($middlewareHi, 2);

        $resolver->resolve(null);

    }

    public function testCloning(): void
    {
        $resolver = new ObjectFieldResolver();

        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $resolver->pipe($middleware);
        $clone = clone $resolver;

        self::assertSame('foo', $resolver->resolve(null)->name);
        self::assertSame('foo', $clone->resolve(null)->name);

        $middlewareHi = \Mockery::mock(MiddlewareInterface::class);
        $middlewareHi->shouldReceive('process')->andReturn(new Webonyx\FieldDefinition(['name' => 'bar']));

        $clone->pipe($middlewareHi, 100); // hi priority

        self::assertSame('foo', $resolver->resolve(null)->name);
        self::assertSame('bar', $clone->resolve(null)->name);
    }
}

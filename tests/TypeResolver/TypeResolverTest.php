<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver;

use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeResolver\CantResolveGraphQLTypeResolver;
use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\Middleware\Next;
use Andi\GraphQL\TypeResolver\Middleware\PipelineInterface;
use Andi\GraphQL\TypeResolver\TypeResolver;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeResolver::class)]
#[UsesClass(CantResolveGraphQLTypeResolver::class)]
#[UsesClass(Next::class)]
final class TypeResolverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $resolver = new TypeResolver();

        self::assertInstanceOf(TypeResolverInterface::class, $resolver);
        self::assertInstanceOf(PipelineInterface::class, $resolver);
    }

    public function testEmptyPipeline(): void
    {
        $resolver = new TypeResolver();

        $this->expectException(CantResolveGraphQLTypeException::class);
        $resolver->resolve(null);
    }

    public function testPipe(): void
    {
        $resolver = new TypeResolver();

        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->once()->andReturn(Webonyx\Type::id());

        $resolver->pipe($middleware);
        $resolver->resolve(null);
    }

    public function testPriority(): void
    {
        $resolver = new TypeResolver();

        $middlewareHi = \Mockery::mock(MiddlewareInterface::class);
        $middlewareHi->shouldReceive('process')->once()->andReturn(Webonyx\Type::id());

        $middlewareLow = \Mockery::mock(MiddlewareInterface::class);
        $middlewareLow->shouldReceive('process')->never()->andReturn(Webonyx\Type::int());

        $resolver->pipe($middlewareLow, 1);
        $resolver->pipe($middlewareHi, 2);

        $resolver->resolve(null);

    }

    public function testCloning(): void
    {
        $resolver = new TypeResolver();

        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->andReturn(Webonyx\Type::id());

        $resolver->pipe($middleware);
        $clone = clone $resolver;

        self::assertSame('ID', $resolver->resolve(null)->name);
        self::assertSame('ID', $clone->resolve(null)->name);

        $middlewareHi = \Mockery::mock(MiddlewareInterface::class);
        $middlewareHi->shouldReceive('process')->andReturn(Webonyx\Type::int());

        $clone->pipe($middlewareHi, 100); // hi priority

        self::assertSame('ID', $resolver->resolve(null)->name);
        self::assertSame('Int', $clone->resolve(null)->name);
    }

}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Exception\NextHandlerIsEmptyException;
use Andi\GraphQL\TypeResolver\CantResolveGraphQLTypeResolver;
use Andi\GraphQL\TypeResolver\Middleware\Next;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Next::class)]
final class NextTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $next = new Next(new \SplPriorityQueue(), new CantResolveGraphQLTypeResolver());

        self::assertInstanceOf(TypeResolverInterface::class, $next);
    }

    public function testRaiseException(): void
    {
        $middleware = \Mockery::mock(TypeResolverInterface::class);
        $middleware->shouldReceive('process')->once()->andReturn(Webonyx\Type::id());

        $queue = new \SplPriorityQueue();
        $queue->insert($middleware, 0);

        $next = new Next($queue, new CantResolveGraphQLTypeResolver());
        self::assertSame('ID', $next->resolve(null)->name);

        $this->expectException(NextHandlerIsEmptyException::class);
        $next->resolve(null);
    }

    public function testCallFallbackResolverWhenQueueIsEmpty(): void
    {
        $resolver = \Mockery::mock(TypeResolverInterface::class);
        $resolver->shouldReceive('resolve')->once()->andReturn(Webonyx\Type::id());

        $next = new Next(new \SplPriorityQueue(), $resolver);

        self::assertSame('ID', $next->resolve(null)->name);
    }
}

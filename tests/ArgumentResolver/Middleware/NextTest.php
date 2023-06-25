<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\ArgumentResolver\CantResolveArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\Next;
use Andi\GraphQL\Exception\NextHandlerIsEmptyException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Next::class)]
class NextTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $next = new Next(new \SplPriorityQueue(), new CantResolveArgumentResolver());

        self::assertInstanceOf(ArgumentResolverInterface::class, $next);
    }

    public function testRaiseException(): void
    {
        $middleware = \Mockery::mock(ArgumentResolverInterface::class);
        $middleware->shouldReceive('process')->once()->andReturn(['foo' => 'bar']);

        $queue = new \SplPriorityQueue();
        $queue->insert($middleware, 0);

        $next = new Next($queue, new CantResolveArgumentResolver());
        self::assertSame(['foo' => 'bar'], $next->resolve(null));

        $this->expectException(NextHandlerIsEmptyException::class);
        $next->resolve(null);
    }

    public function testCallFallbackResolverWhenQueueIsEmpty(): void
    {
        $resolver = \Mockery::mock(ArgumentResolverInterface::class);
        $resolver->shouldReceive('resolve')->once()->andReturn(['foo' => 'bar']);

        $next = new Next(new \SplPriorityQueue(), $resolver);

        self::assertSame(['foo' => 'bar'], $next->resolve(null));
    }
}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Exception\NextHandlerIsEmptyException;
use Andi\GraphQL\ObjectFieldResolver\CantResolveObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\Middleware\Next;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Next::class)]
class NextTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $next = new Next(new \SplPriorityQueue(), new CantResolveObjectFieldResolver());

        self::assertInstanceOf(ObjectFieldResolverInterface::class, $next);
    }

    public function testRaiseException(): void
    {
        $middleware = \Mockery::mock(ObjectFieldResolverInterface::class);
        $middleware->shouldReceive('process')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $queue = new \SplPriorityQueue();
        $queue->insert($middleware, 0);

        $next = new Next($queue, new CantResolveObjectFieldResolver());
        self::assertSame('foo', $next->resolve(null)->name);

        $this->expectException(NextHandlerIsEmptyException::class);
        $next->resolve(null);
    }

    public function testCallFallbackResolverWhenQueueIsEmpty(): void
    {
        $resolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $resolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $next = new Next(new \SplPriorityQueue(), $resolver);

        self::assertSame('foo', $next->resolve(null)->name);
    }
}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Exception\NextHandlerIsEmptyException;
use Andi\GraphQL\InputObjectFieldResolver\CantResolveInputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\Next;
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
        $next = new Next(new \SplPriorityQueue(), new CantResolveInputObjectFieldResolver());

        self::assertInstanceOf(InputObjectFieldResolverInterface::class, $next);
    }

    public function testRaiseException(): void
    {
        $middleware = \Mockery::mock(InputObjectFieldResolverInterface::class);
        $middleware->shouldReceive('process')->once()->andReturn(new Webonyx\InputObjectField(['name' => 'foo']));

        $queue = new \SplPriorityQueue();
        $queue->insert($middleware, 0);

        $next = new Next($queue, new CantResolveInputObjectFieldResolver());
        self::assertSame('foo', $next->resolve(null)->name);

        $this->expectException(NextHandlerIsEmptyException::class);
        $next->resolve(null);
    }

    public function testCallFallbackResolverWhenQueueIsEmpty(): void
    {
        $resolver = \Mockery::mock(InputObjectFieldResolverInterface::class);
        $resolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\InputObjectField(['name' => 'foo']));

        $next = new Next(new \SplPriorityQueue(), $resolver);

        self::assertSame('foo', $next->resolve(null)->name);
    }
}

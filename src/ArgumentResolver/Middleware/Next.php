<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Exception\NextHandlerIsEmptyException;
use SplPriorityQueue;

final class Next implements ArgumentResolverInterface
{
    private SplPriorityQueue $queue;

    public function __construct(
        SplPriorityQueue $queue,
        private readonly ArgumentResolverInterface $fallbackResolver,
    ) {
        $this->queue = clone $queue;
    }

    public function resolve(mixed $argument): array
    {
        if (! isset($this->queue)) {
            throw new NextHandlerIsEmptyException('Cannot invoke pipeline resolver more than once');
        }

        if ($this->queue->isEmpty()) {
            unset($this->queue);

            return $this->fallbackResolver->resolve($argument);
        }

        /** @var MiddlewareInterface $middleware */
        $middleware = $this->queue->extract();

        $next = clone $this;
        unset($this->queue);

        return $middleware->process($argument, $next);
    }
}

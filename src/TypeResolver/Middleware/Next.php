<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Exception\NextHandlerIsEmptyException;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use SplPriorityQueue;

final class Next implements TypeResolverInterface
{
    private SplPriorityQueue $queue;

    public function __construct(
        SplPriorityQueue $queue,
        private readonly TypeResolverInterface $fallbackResolver,
    ) {
        $this->queue = clone $queue;
    }

    public function resolve(mixed $type): Webonyx\Type
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (! isset($this->queue)) {
            throw new NextHandlerIsEmptyException('Cannot invoke pipeline resolver more than once');
        }

        if ($this->queue->isEmpty()) {
            unset($this->queue);

            return $this->fallbackResolver->resolve($type);
        }

        /** @var MiddlewareInterface $middleware */
        $middleware = $this->queue->extract();

        $next = clone $this;
        unset($this->queue);

        return $middleware->process($type, $next);
    }
}

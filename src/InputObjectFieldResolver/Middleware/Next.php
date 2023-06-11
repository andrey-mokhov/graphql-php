<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Exception\NextHandlerIsEmptyException;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use SplPriorityQueue;

final class Next implements InputObjectFieldResolverInterface
{
    private SplPriorityQueue $queue;

    public function __construct(
        SplPriorityQueue $queue,
        private readonly InputObjectFieldResolverInterface $fallbackResolver,
    ) {
        $this->queue = clone $queue;
    }

    public function resolve(mixed $argument): Webonyx\InputObjectField
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

<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Exception\NextHandlerIsEmptyException;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use SplPriorityQueue;

final class Next implements ObjectFieldResolverInterface
{
    private SplPriorityQueue $queue;

    public function __construct(
        SplPriorityQueue $queue,
        private readonly ObjectFieldResolverInterface $fallbackResolver,
    ) {
        $this->queue = clone $queue;
    }

    public function resolve(mixed $field): Webonyx\FieldDefinition
    {
        if (! isset($this->queue)) {
            throw new NextHandlerIsEmptyException('Cannot invoke pipeline resolver more than once');
        }

        if ($this->queue->isEmpty()) {
            unset($this->queue);

            return $this->fallbackResolver->resolve($field);
        }

        /** @var MiddlewareInterface $middleware */
        $middleware = $this->queue->extract();

        $next = clone $this;
        unset($this->queue);

        return $middleware->process($field, $next);
    }
}

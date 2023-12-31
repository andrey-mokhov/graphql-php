<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver;

use Andi\GraphQL\ArgumentResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ArgumentResolver\Middleware\Next;
use Andi\GraphQL\ArgumentResolver\Middleware\PipelineInterface;
use SplPriorityQueue;

final class ArgumentResolver implements PipelineInterface
{
    private SplPriorityQueue $pipeline;

    private int $secondPriority = \PHP_INT_MAX;

    public function __construct()
    {
        $this->pipeline = new SplPriorityQueue();
    }

    public function __clone()
    {
        $this->pipeline = clone $this->pipeline;
    }

    public function resolve(mixed $argument): array
    {
        return (new Next($this->pipeline, new CantResolveArgumentResolver()))->resolve($argument);
    }

    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void
    {
        $this->pipeline->insert($middleware, [$priority, $this->secondPriority--]);
    }
}

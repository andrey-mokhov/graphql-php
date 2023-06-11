<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver;

use Andi\GraphQL\InputObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\Next;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\PipelineInterface;
use GraphQL\Type\Definition as Webonyx;
use SplPriorityQueue;

final class InputObjectFieldResolver implements PipelineInterface
{
    private SplPriorityQueue $pipeline;

    private int $secondPriority = PHP_INT_MAX;

    public function __construct()
    {
        $this->pipeline = new SplPriorityQueue();
    }

    public function __clone()
    {
        $this->pipeline = clone $this->pipeline;
    }

    public function resolve(mixed $field): Webonyx\InputObjectField
    {
        return (new Next($this->pipeline, new CantResolveInputObjectFieldResolver()))->resolve($field);
    }

    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void
    {
        $this->pipeline->insert($middleware, [$priority, $this->secondPriority--]);
    }
}

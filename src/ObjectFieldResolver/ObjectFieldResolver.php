<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver;

use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\Next;
use Andi\GraphQL\ObjectFieldResolver\Middleware\PipelineInterface;
use GraphQL\Type\Definition as Webonyx;
use SplPriorityQueue;

final class ObjectFieldResolver implements PipelineInterface
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

    public function resolve(mixed $field): Webonyx\FieldDefinition
    {
        return (new Next($this->pipeline, new CantResolveObjectFieldResolver()))->resolve($field);
    }

    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void
    {
        $this->pipeline->insert($middleware, [$priority, $this->secondPriority--]);
    }
}

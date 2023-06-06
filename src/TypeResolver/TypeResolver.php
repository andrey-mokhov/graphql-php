<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver;

use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\Middleware\Next;
use Andi\GraphQL\TypeResolver\Middleware\PipelineInterface;
use GraphQL\Type\Definition as Webonyx;
use SplPriorityQueue;

final class TypeResolver implements PipelineInterface
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

    /**
     * @param class-string $class
     *
     * @return Webonyx\Type
     */
    public function resolve(string $class): Webonyx\Type
    {
        return $this->process($class, new CantResolveGraphQLTypeResolver());
    }

    /**
     * @param class-string $class
     * @param TypeResolverInterface $typeResolver
     *
     * @return Webonyx\Type
     */
    public function process(string $class, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        return (new Next($this->pipeline, $typeResolver))->resolve($class);
    }

    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void
    {
        $this->pipeline->insert($middleware, [$priority, $this->secondPriority--]);
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;

interface PipelineInterface extends ObjectFieldResolverInterface
{
    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void;
}

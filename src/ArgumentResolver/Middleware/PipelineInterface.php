<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;

interface PipelineInterface extends ArgumentResolverInterface
{
    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void;
}

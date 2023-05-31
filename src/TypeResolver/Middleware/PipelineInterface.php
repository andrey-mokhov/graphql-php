<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\TypeResolver\TypeResolverInterface;

interface PipelineInterface extends TypeResolverInterface, MiddlewareInterface
{
    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void;
}

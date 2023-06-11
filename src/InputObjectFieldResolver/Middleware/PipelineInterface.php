<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;

interface PipelineInterface extends InputObjectFieldResolverInterface
{
    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void;
}

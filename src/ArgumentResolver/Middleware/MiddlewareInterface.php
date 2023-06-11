<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;

interface MiddlewareInterface
{
    public function process(mixed $argument, ArgumentResolverInterface $argumentResolver): array;
}

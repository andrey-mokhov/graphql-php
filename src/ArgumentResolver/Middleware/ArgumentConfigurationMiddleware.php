<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use GraphQL\Type\Definition as Webonyx;

final class ArgumentConfigurationMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function process(mixed $argument, ArgumentResolverInterface $argumentResolver): array
    {
        $isConfig = is_array($argument)
            && isset($argument['name'], $argument['type'])
            && $argument['type'] instanceof Webonyx\Type;

        return $isConfig
            ? $argument
            : $argumentResolver->resolve($argument);
    }
}

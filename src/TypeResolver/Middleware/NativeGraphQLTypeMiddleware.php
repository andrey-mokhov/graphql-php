<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Psr\Container\ContainerInterface;

final class NativeGraphQLTypeMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function process(string $class, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        if (is_subclass_of($class, Webonyx\Type::class)) {
            return $this->container->get($class);
        }

        return $typeResolver->resolve($class);
    }
}

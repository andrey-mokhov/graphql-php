<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Psr\Container\ContainerInterface;

final class WebonyxGraphQLTypeMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 3072;

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        return \is_string($type) && \is_subclass_of($type, Webonyx\Type::class)
            ? $this->container->get($type)
            : $typeResolver->resolve($type);
    }
}

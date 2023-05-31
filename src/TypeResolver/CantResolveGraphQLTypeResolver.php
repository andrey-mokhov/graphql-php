<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver;

use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;

final class CantResolveGraphQLTypeResolver implements TypeResolverInterface
{
    public function resolve(string $class): never
    {
        throw new CantResolveGraphQLTypeException(sprintf('Can\'t resolve GraphQL type for: "%s"', $class));
    }
}

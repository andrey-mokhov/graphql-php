<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver;

use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;

final class CantResolveGraphQLTypeResolver implements TypeResolverInterface
{
    public function resolve(mixed $type): never
    {
        throw new CantResolveGraphQLTypeException('Can\'t resolve GraphQL type');
    }
}

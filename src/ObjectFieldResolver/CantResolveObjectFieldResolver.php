<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver;

use Andi\GraphQL\Exception\CantResolveObjectFieldException;

final class CantResolveObjectFieldResolver implements ObjectFieldResolverInterface
{
    public function resolve(mixed $field): never
    {
        throw new CantResolveObjectFieldException('Can\'t resolve ObjectField configuration');
    }
}

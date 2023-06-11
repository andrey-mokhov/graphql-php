<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver;

use Andi\GraphQL\Exception\CantResolveArgumentException;

final class CantResolveArgumentResolver implements ArgumentResolverInterface
{
    public function resolve(mixed $argument): never
    {
        throw new CantResolveArgumentException('Can\'t resolve Argument configuration');
    }
}

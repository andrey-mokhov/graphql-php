<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver;

use Andi\GraphQL\Exception\CantResolveInputObjectFieldException;

final class CantResolveInputObjectFieldResolver implements InputObjectFieldResolverInterface
{
    public function resolve(mixed $field): never
    {
        throw new CantResolveInputObjectFieldException('Can\'t resolve InputObjectField');
    }
}

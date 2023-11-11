<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Type\UnionTypeInterface;

abstract class AbstractUnionType extends AbstractType implements UnionTypeInterface
{
    protected iterable $types;

    public function getTypes(): iterable
    {
        return $this->types;
    }
}

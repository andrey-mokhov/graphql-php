<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

interface UnionTypeInterface extends TypeInterface
{
    /**
     * @return iterable<string>
     */
    public function getTypes(): iterable;
}

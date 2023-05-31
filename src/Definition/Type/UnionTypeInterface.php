<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use Andi\GraphQL\Definition\DefinitionInterface;

interface UnionTypeInterface extends DefinitionInterface
{
    /**
     * @return iterable<string>
     */
    public function getTypes(): iterable;
}

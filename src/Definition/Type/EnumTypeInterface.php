<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use Andi\GraphQL\Definition\DefinitionInterface;

interface EnumTypeInterface extends DefinitionInterface
{
    public function getValues(): iterable;
}

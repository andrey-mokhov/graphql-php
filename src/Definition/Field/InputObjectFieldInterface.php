<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

use Andi\GraphQL\Definition\DefinitionInterface;

interface InputObjectFieldInterface extends DefinitionInterface, DeprecationReasonAwareInterface, TypeAwareInterface
{
    public function hasDefaultValue(): bool;
}

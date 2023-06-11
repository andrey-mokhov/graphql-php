<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

interface DynamicObjectTypeInterface
{
    public function addAdditionalField(mixed $field): static;
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;

interface DynamicObjectTypeInterface
{
    public function addAdditionalField(ObjectFieldInterface $field): static;
}

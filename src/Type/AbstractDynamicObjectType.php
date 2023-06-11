<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

abstract class AbstractDynamicObjectType extends AbstractObjectType implements DynamicObjectTypeInterface
{
    protected array $fields = [];

    protected array $additionalFields = [];

    public function addAdditionalField(mixed $field): static
    {
        $this->additionalFields[] = $field;

        return $this;
    }

    public function getFields(): iterable
    {
        yield from $this->fields;
        yield from $this->additionalFields;
    }
}

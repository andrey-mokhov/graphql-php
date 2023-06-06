<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;

abstract class AbstractDynamicObjectType extends AbstractObjectType implements DynamicObjectTypeInterface
{
    /**
     * @var array<int, ObjectFieldInterface>
     */
    protected array $fields = [];

    protected array $additionalFields = [];

    public function addAdditionalField(ObjectFieldInterface $field): static
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

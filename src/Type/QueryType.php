<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ObjectTypeInterface;

final class QueryType implements ObjectTypeInterface, DynamicObjectTypeInterface
{
    private array $fields = [];

    public function getName(): string
    {
        return 'Query';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield from $this->fields;
    }

    public function addAdditionalField(mixed $field): static
    {
        $this->fields[] = $field;

        return $this;
    }
}

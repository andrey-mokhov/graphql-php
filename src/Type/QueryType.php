<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Type\ObjectTypeInterface;

final class QueryType implements ObjectTypeInterface, DynamicObjectTypeInterface
{
    /** @var array<int,ObjectFieldInterface> */
    private array $fields = [];

    public function getName(): string
    {
        return 'Query';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    /**
     * @return iterable<ObjectFieldInterface>
     */
    public function getFields(): iterable
    {
        yield from $this->fields;
    }

    public function addAdditionalField(ObjectFieldInterface $field): static
    {
        $this->fields[] = $field;

        return $this;
    }
}

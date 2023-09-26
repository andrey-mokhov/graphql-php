<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Type\UnionTypeInterface;

abstract class AbstractUnionType implements UnionTypeInterface
{
    protected string $name;

    protected string $description;

    protected iterable $types;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    public function getTypes(): iterable
    {
        return $this->types;
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Argument;

use Andi\GraphQL\Definition\Field\ArgumentInterface;
use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;

abstract class AbstractArgument implements ArgumentInterface
{
    protected readonly string $name;

    protected readonly string $description;

    protected readonly string $type;

    protected readonly int $typeMode;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeMode(): int
    {
        return $this->typeMode ?? 0;
    }

    public function hasDefaultValue(): bool
    {
        return $this instanceof DefaultValueAwareInterface;
    }
}

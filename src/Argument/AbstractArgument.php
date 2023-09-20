<?php

declare(strict_types=1);

namespace Andi\GraphQL\Argument;

use Andi\GraphQL\Definition\Field\ArgumentInterface;
use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;

abstract class AbstractArgument implements ArgumentInterface
{
    private readonly string $description;

    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly int $typeMode = 0,
        ?string $description = null,
    ) {
        if (null !== $description) {
            $this->description = $description;
        }
    }


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

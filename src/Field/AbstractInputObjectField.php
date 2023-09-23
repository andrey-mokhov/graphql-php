<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use Andi\GraphQL\Definition\Field\DeprecationReasonAwareInterface;
use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;

abstract class AbstractInputObjectField implements InputObjectFieldInterface, DeprecationReasonAwareInterface
{
    private readonly string $description;

    private readonly string $deprecationReason;

    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly int $typeMode = 0,
        ?string $description = null,
        ?string $deprecationReason = null,
    ) {
        if (null !== $description) {
            $this->description = $description;
        }

        if (null !== $deprecationReason) {
            $this->deprecationReason = $deprecationReason;
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
        return $this->typeMode;
    }

    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason ?? null;
    }

    public function hasDefaultValue(): bool
    {
        return $this instanceof DefaultValueAwareInterface;
    }
}

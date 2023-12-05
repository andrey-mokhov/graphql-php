<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use Andi\GraphQL\Definition\Field\EnumValueInterface;

final class EnumValue implements EnumValueInterface
{
    private readonly string $description;
    private readonly string $deprecationReason;

    public function __construct(
        private readonly string $name,
        private readonly mixed $value,
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
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->description ?? null;
    }

    public function getDeprecationReason(): ?string
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->deprecationReason ?? null;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}

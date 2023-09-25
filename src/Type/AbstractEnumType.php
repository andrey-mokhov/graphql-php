<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\EnumValueInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;
use Andi\GraphQL\Field\EnumValue;

class AbstractEnumType implements EnumTypeInterface
{
    protected string $name;
    protected string $description;
    protected iterable $values;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    public function getValues(): iterable
    {
        foreach ($this->values as $name => $value) {
            if ($value instanceof EnumValueInterface) {
                yield $value;
            } elseif (is_string($name)) {
                if (is_array($value)) {
                    yield new EnumValue(
                        name: $name,
                        value: $value['value'] ?? $name,
                        description: $value['description'] ?? null,
                        deprecationReason: $value['deprecationReason'] ?? null,
                    );
                } else {
                    yield new EnumValue(name: $name, value: $value);
                }

            } elseif (is_string($value)) {
                yield new EnumValue(name: $value, value: $value);
            }
        }
    }
}

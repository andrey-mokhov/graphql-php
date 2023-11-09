<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\EnumValueInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;
use Andi\GraphQL\Exception\CantResolveEnumTypeException;
use Andi\GraphQL\Field\EnumValue;

abstract class AbstractEnumType implements EnumTypeInterface
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
            } elseif (is_array($value)) {
                $valueName = $value['name'] ?? $name;
                if (! is_string($valueName)) {
                    throw new CantResolveEnumTypeException('Can\'t resolve EnumValue: wrong value configuration');
                }
                yield new EnumValue(
                    name: $valueName,
                    value: $value['value'] ?? $valueName,
                    description: $value['description'] ?? null,
                    deprecationReason: $value['deprecationReason'] ?? null,
                );
            } elseif (is_string($name)) {
                yield new EnumValue(name: $name, value: $value);
            } elseif (is_string($value)) {
                yield new EnumValue(name: $value, value: $value);
            } else {
                throw new CantResolveEnumTypeException('Can\'t resolve EnumValue: wrong value configuration');
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;

final class InputObjectField extends AbstractInputObjectField implements DefaultValueAwareInterface
{
    private readonly mixed $defaultValue;

    public function __construct(
        string $name,
        string $type,
        int $typeMode = 0,
        ?string $description = null,
        ?string $deprecationReason = null,
        mixed $defaultValue = null,
    ) {
        parent::__construct($name, $type, $typeMode, $description, $deprecationReason);

        if (func_num_args() >= 6) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function hasDefaultValue(): bool
    {
        return isset($this->defaultValue)
            || (new \ReflectionProperty($this, 'defaultValue'))->isInitialized($this);
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
}

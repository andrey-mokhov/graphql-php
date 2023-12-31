<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

use Attribute;
use ReflectionProperty;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class InputObjectField extends AbstractField
{
    public readonly mixed $defaultValue;

    public function __construct(
        ?string $name = null,
        ?string $description = null,
        ?string $type = null,
        ?int $mode = null,
        ?string $deprecationReason = null,
        mixed $defaultValue = null,
    ) {
        parent::__construct($name, $description, $type, $mode, $deprecationReason);

        if (\func_num_args() >= 6) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function hasDefaultValue(): bool
    {
        return (new ReflectionProperty($this, 'defaultValue'))->isInitialized($this);
    }
}

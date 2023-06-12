<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

use Attribute;
use ReflectionProperty;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_PARAMETER), NamedArgumentConstructor]
final class Argument
{
    public readonly mixed $defaultValue;

    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $type = null,
        public readonly ?string $deprecationReason = null,
        public readonly ?string $factory = null,
        mixed $defaultValue = null,
    ) {
        if (func_num_args() === 6) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function hasDefaultValue(): bool
    {
        return (new ReflectionProperty($this, 'defaultValue'))->isInitialized($this);
    }
}

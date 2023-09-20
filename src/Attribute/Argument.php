<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

use Attribute;
use ReflectionProperty;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_PARAMETER), NamedArgumentConstructor]
final class Argument extends AbstractDefinition
{
    public readonly mixed $defaultValue;

    public function __construct(
        ?string $name = null,
        ?string $description = null,
        public readonly ?string $type = null,
        public readonly ?string $deprecationReason = null,
        mixed $defaultValue = null,
    ) {
        parent::__construct($name, $description);

        if (func_num_args() >= 5) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function hasDefaultValue(): bool
    {
        return (new ReflectionProperty($this, 'defaultValue'))->isInitialized($this);
    }
}

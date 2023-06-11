<?php

declare(strict_types=1);

namespace Andi\GraphQL\Argument;

use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use ReflectionProperty;

class Argument extends AbstractArgument implements DefaultValueAwareInterface
{
    protected readonly string $description;

    protected readonly mixed $defaultValue;

    public function __construct(
        protected readonly string $name,
        protected readonly string $type,
        protected readonly int $typeMode = 0,
        ?string $description = null,
        mixed $defaultValue = null,
    ) {
        if (null !== $description) {
            $this->description = $description;
        }

        if (func_num_args() === 5) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function hasDefaultValue(): bool
    {
        return (new ReflectionProperty($this, 'defaultValue'))->isInitialized($this);
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
}

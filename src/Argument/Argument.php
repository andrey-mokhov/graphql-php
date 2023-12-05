<?php

declare(strict_types=1);

namespace Andi\GraphQL\Argument;

use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use Andi\GraphQL\Definition\Field\DeprecationReasonAwareInterface;

class Argument extends AbstractArgument implements DefaultValueAwareInterface, DeprecationReasonAwareInterface
{
    private readonly string $deprecationReason;
    private readonly mixed $defaultValue;

    public function __construct(
        string  $name,
        string  $type,
        int $mode = 0,
        ?string $description = null,
        ?string $deprecationReason = null,
        mixed $defaultValue = null,
    ) {
        parent::__construct($name, $type, $mode, $description);

        if (null !== $deprecationReason) {
            $this->deprecationReason = $deprecationReason;
        }

        if (func_num_args() >= 6) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function getDeprecationReason(): ?string
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->deprecationReason ?? null;
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

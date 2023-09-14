<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;

abstract class AbstractObjectField implements ObjectFieldInterface, ArgumentsAwareInterface
{
    protected string $name;

    protected string $description;

    protected string $type;

    protected int $typeMode;

    protected string $deprecationReason;

    protected iterable $arguments;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason ?? null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeMode(): int
    {
        return $this->typeMode ?? 0;
    }

    public function getArguments(): iterable
    {
        return $this->arguments ?? [];
    }
}

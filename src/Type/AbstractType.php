<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\DefinitionInterface;

abstract class AbstractType implements DefinitionInterface
{
    protected string $name;

    protected string $description;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }
}

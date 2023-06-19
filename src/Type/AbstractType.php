<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Type\TypeInterface;

abstract class AbstractType implements TypeInterface
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

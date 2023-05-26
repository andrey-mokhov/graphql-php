<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition;

interface DefinitionInterface
{
    public function getName(): string;

    public function getDescription(): ?string;
}

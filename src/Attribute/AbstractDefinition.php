<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

abstract class AbstractDefinition
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
    ) {
    }
}

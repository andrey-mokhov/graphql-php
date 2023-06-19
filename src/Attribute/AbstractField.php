<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

abstract class AbstractField extends AbstractDefinition
{
    public function __construct(
        ?string $name = null,
        ?string $description = null,
        public readonly ?string $type = null,
        public readonly ?string $deprecationReason = null,
    ) {
        parent::__construct($name, $description);
    }
}

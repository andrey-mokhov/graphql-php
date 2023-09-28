<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE), NamedArgumentConstructor]
final class AdditionalField extends AbstractField
{
    public function __construct(
        public readonly string $targetType,
        ?string $name = null,
        ?string $description = null,
        ?string $type = null,
        ?string $deprecationReason = null
    ) {
        parent::__construct($name, $description, $type, $deprecationReason);
    }
}

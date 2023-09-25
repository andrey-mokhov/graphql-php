<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT), NamedArgumentConstructor]
final class EnumCase extends AbstractDefinition
{
    public function __construct(
        ?string $name = null,
        ?string $description = null,
        public readonly ?string $deprecationReason = null,
    ) {
        parent::__construct($name, $description);
    }
}

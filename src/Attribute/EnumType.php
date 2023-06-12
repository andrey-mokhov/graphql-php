<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class EnumType
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
    ) {
        $q = 12;
    }
}

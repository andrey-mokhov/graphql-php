<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Attribute\AbstractField;

final class ReflectionMethodWithAttribute
{
    public function __construct(
        public readonly \ReflectionMethod $method,
        public readonly AbstractField $attribute,
    ) {
    }
}

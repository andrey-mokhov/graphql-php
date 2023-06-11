<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use GraphQL\Type\Definition\ResolveInfo;

interface IsTypeOfAwareInterface
{
    public function isTypeOf(mixed $value, mixed $context, ResolveInfo $info): bool;
}

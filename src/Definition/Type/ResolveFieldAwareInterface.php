<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use GraphQL\Type\Definition\ResolveInfo;

interface ResolveFieldAwareInterface
{
    public function resolveField(mixed $value, array $args, mixed $context, ResolveInfo $info): mixed;
}

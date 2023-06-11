<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use GraphQL\Type\Definition\ResolveInfo;

interface ResolveTypeAwareInterface
{
    public function resolveType(mixed $value, mixed $context, ResolveInfo $info): string;
}

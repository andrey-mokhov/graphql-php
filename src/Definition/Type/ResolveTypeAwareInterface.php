<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use GraphQL\Type\Definition as Webonyx;

interface ResolveTypeAwareInterface
{
    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string;
}

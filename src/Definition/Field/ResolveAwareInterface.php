<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

use GraphQL\Type\Definition\ResolveInfo;

interface ResolveAwareInterface
{
    public function resolve(mixed $objectValue, array $args, mixed $context, ResolveInfo $info): mixed;
}

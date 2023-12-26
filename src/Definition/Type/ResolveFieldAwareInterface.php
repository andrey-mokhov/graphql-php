<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use GraphQL\Type\Definition as Webonyx;

interface ResolveFieldAwareInterface
{
    public function resolveField(mixed $value, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed;
}

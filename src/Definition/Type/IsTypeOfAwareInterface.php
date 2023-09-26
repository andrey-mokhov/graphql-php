<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use GraphQL\Type\Definition as Webonyx;

interface IsTypeOfAwareInterface
{
    public function isTypeOf(mixed $value, mixed $context, Webonyx\ResolveInfo $info): bool;
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver;

use GraphQL\Type\Definition as Webonyx;

interface TypeResolverInterface
{
    public function resolve(mixed $type): Webonyx\Type;
}

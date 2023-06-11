<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver;

use GraphQL\Type\Definition as Webonyx;

interface ObjectFieldResolverInterface
{
    public function resolve(mixed $field): Webonyx\FieldDefinition;
}

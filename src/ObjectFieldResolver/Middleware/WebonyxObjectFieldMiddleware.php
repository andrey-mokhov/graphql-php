<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;

final class WebonyxObjectFieldMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function process(mixed $field, ObjectFieldResolverInterface $fieldResolver): Webonyx\FieldDefinition
    {
        return is_object($field) && $field instanceof Webonyx\FieldDefinition
            ? $field
            : $fieldResolver->resolve($field);
    }
}

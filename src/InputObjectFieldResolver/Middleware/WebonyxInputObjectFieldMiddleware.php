<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;

final class WebonyxInputObjectFieldMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function process(mixed $field, InputObjectFieldResolverInterface $fieldResolver): Webonyx\InputObjectField
    {
        return $field instanceof Webonyx\InputObjectField
            ? $field
            : $fieldResolver->resolve($field);
    }
}

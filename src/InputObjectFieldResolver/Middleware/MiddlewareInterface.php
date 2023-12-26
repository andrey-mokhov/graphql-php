<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;

interface MiddlewareInterface
{
    public function process(mixed $field, InputObjectFieldResolverInterface $fieldResolver): Webonyx\InputObjectField;
}

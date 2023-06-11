<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver;

use GraphQL\Type\Definition as Webonyx;

interface InputObjectFieldResolverInterface
{
    public function resolve(mixed $field): Webonyx\InputObjectField;
}

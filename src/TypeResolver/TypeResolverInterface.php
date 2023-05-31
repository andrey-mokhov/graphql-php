<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver;

use GraphQL\Type\Definition as Webonyx;

interface TypeResolverInterface
{
    /**
     * @param class-string $class
     *
     * @return Webonyx\Type
     */
    public function resolve(string $class): Webonyx\Type;
}

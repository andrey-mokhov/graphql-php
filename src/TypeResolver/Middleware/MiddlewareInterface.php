<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;

interface MiddlewareInterface
{
    /**
     * @param class-string $class
     * @param TypeResolverInterface $typeResolver
     *
     * @return Webonyx\Type
     */
    public function process(string $class, TypeResolverInterface $typeResolver): Webonyx\Type;
}

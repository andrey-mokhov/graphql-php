<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;

interface MiddlewareInterface
{
    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type;
}

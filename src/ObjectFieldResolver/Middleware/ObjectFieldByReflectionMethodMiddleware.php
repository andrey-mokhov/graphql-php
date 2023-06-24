<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\ObjectField;

final class ObjectFieldByReflectionMethodMiddleware extends AbstractObjectFieldByReflectionMethodMiddleware
{
    public const PRIORITY = 4096;

    protected string $targetAttribute = ObjectField::class;
}

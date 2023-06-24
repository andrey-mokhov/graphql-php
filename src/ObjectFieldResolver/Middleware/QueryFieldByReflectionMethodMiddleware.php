<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\QueryField;

final class QueryFieldByReflectionMethodMiddleware extends AbstractOuterObjectFieldByReflectionMethodMiddleware
{
    public const PRIORITY = ObjectFieldByReflectionMethodMiddleware::PRIORITY + 256;

    protected string $targetAttribute = QueryField::class;
}

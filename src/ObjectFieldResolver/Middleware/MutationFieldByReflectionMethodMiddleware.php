<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\MutationField;

final class MutationFieldByReflectionMethodMiddleware extends AbstractOuterObjectFieldByReflectionMethodMiddleware
{
    public const PRIORITY = ObjectFieldByReflectionMethodMiddleware::PRIORITY + 192;

    protected string $targetAttribute = MutationField::class;
}

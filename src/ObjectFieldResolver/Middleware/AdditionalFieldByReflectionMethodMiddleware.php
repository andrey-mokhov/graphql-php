<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\AdditionalField;

final class AdditionalFieldByReflectionMethodMiddleware extends AbstractOuterObjectFieldByReflectionMethodMiddleware
{
    public const PRIORITY = ObjectFieldByReflectionMethodMiddleware::PRIORITY + 128;

    protected string $targetAttribute = AdditionalField::class;
}

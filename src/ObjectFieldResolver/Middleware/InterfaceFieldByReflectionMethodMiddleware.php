<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\InterfaceField;

final class InterfaceFieldByReflectionMethodMiddleware extends AbstractObjectFieldByReflectionMethodMiddleware
{
    public const PRIORITY = ObjectFieldByReflectionMethodMiddleware::PRIORITY + 64;

    protected string $targetAttribute = InterfaceField::class;
}

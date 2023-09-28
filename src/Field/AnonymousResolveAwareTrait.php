<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use GraphQL\Type\Definition as Webonyx;

/**
 * @internal
 * @psalm-internal Andi\GraphQL
 */
trait AnonymousResolveAwareTrait
{
    protected readonly mixed $resolve;

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        return call_user_func($this->resolve, $objectValue, $args, $context, $info);
    }
}

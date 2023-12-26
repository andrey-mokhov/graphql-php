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
    /**
     * @var callable
     */
    protected readonly mixed $resolveFn;

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        return \call_user_func($this->resolveFn, $objectValue, $args, $context, $info);
    }
}

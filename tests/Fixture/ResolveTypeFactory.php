<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use GraphQL\Type\Definition as Webonyx;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class ResolveTypeFactory
{
    public function __invoke(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        return 'FooObjectType';
    }
}

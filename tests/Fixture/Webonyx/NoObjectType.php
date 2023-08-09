<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture\Webonyx;

use GraphQL\Type\Definition\EnumType;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class NoObjectType extends EnumType
{
    public string $name = 'NoObjectType';
}

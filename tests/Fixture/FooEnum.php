<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\EnumType;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
#[EnumType]
enum FooEnum
{
    case foo;

    case bar;
}

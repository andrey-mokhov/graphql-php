<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\EnumType;
use Andi\GraphQL\Attribute\EnumValue;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
#[EnumType(name: 'FooEnum', description: 'FooEnum description')]
enum AttributedEnum
{
    #[EnumValue(name: 'barValue', description: 'bar description', deprecationReason: 'bar deprecation reason')]
    case bar;
}

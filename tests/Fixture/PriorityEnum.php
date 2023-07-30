<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\EnumType;

/**
 * Low priority description.
 *
 * @internal
 * @psalm-internal Andi\Tests
 */
#[EnumType(name: 'HiPriorityEnum', description: 'Hi priority description')]
enum PriorityEnum
{
    case foo;
}

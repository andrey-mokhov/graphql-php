<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;

/**
 * ObjectType description.
 *
 * @internal
 * @psalm-internal Andi\Tests
 */
#[ObjectType]
class SimpleObjectType
{
    #[ObjectField]
    private int $foo;

    #[ObjectField]
    public function getBar(): string
    {
    }
}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\InterfaceField;
use Andi\GraphQL\Attribute\InterfaceType;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
#[InterfaceType]
interface FooInterfaceType
{
    #[InterfaceField]
    public function getFoo(): string;
}

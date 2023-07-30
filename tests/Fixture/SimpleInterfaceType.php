<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\InterfaceField;
use Andi\GraphQL\Attribute\InterfaceType;

/**
 * InterfaceType description.
 *
 * @internal
 * @psalm-internal Andi\Tests
 */
#[InterfaceType(resolveType: ResolveTypeFactory::class)]
interface SimpleInterfaceType
{
    #[InterfaceField]
    public function getFoo(): string;
}

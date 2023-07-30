<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\InterfaceField;
use Andi\GraphQL\Attribute\InterfaceType;
use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use GraphQL\Type\Definition as Webonyx;

/**
 * Low priority description.
 *
 * @internal
 * @psalm-internal Andi\Tests
 */
#[InterfaceType(name: 'HiPriorityInterfaceType', description: 'Hi priority description')]
class PriorityInterfaceType implements ResolveTypeAwareInterface
{
    #[InterfaceField]
    public function getFoo(): int
    {
        return 15;
    }

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        return 'FooObjectType';
    }
}

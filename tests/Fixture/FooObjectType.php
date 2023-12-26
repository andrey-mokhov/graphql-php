<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
#[ObjectType]
class FooObjectType implements FooInterfaceType, InterfacesAwareInterface
{
    #[ObjectField]
    public function getFoo(): string
    {
        return 'foo';
    }

    public function getInterfaces(): iterable
    {
        yield 'FooInterfaceType';
    }
}

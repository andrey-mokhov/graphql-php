<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;
use GraphQL\Type\Definition as Webonyx;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Low priority description.
 *
 * @internal
 * @psalm-internal Andi\Tests
 */
#[ObjectType(name: 'HiPriorityObjectType', description: 'Hi priority description')]
class PriorityObjectType implements
    FieldsAwareInterface,
    InterfacesAwareInterface,
    IsTypeOfAwareInterface,
    ResolveFieldAwareInterface
{
    public function getFields(): iterable
    {
        yield new class implements ObjectFieldInterface {
            public function getName(): string
            {
                return 'foo';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function getDeprecationReason(): ?string
            {
                return null;
            }

            public function getType(): string
            {
                return Webonyx\StringType::class;
            }

            public function getMode(): int
            {
                return 0;
            }
        };
    }

    public function getInterfaces(): iterable
    {
        yield 'FooInterface';
    }

    public function isTypeOf(mixed $value, mixed $context, ResolveInfo $info): bool
    {
        return false;
    }

    public function resolveField(mixed $value, array $args, mixed $context, ResolveInfo $info): mixed
    {
        return 15;
    }
}

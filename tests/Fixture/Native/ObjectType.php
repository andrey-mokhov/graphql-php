<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture\Native;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;
use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;
use GraphQL\Type\Definition as Webonyx;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class ObjectType implements
    ObjectTypeInterface,
    InterfacesAwareInterface,
    IsTypeOfAwareInterface,
    ResolveFieldAwareInterface
{
    public function getName(): string
    {
        return 'ObjectType';
    }

    public function getDescription(): ?string
    {
        return 'ObjectType description';
    }

    public function getFields(): iterable
    {
        yield new class implements ObjectFieldInterface {

            public function getName(): string
            {
                return 'field';
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
                return Webonyx\Type::ID;
            }

            public function getTypeMode(): int
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
        return 'object-type';
    }
}

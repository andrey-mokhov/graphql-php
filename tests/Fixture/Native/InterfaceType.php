<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture\Native;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use GraphQL\Type\Definition as Webonyx;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class InterfaceType implements InterfaceTypeInterface, ResolveTypeAwareInterface
{
    public function getName(): string
    {
        return 'InterfaceType';
    }

    public function getDescription(): ?string
    {
        return 'InterfaceType description';
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
                return Webonyx\IDType::class;
            }

            public function getMode(): int
            {
                return 0;
            }
        };
    }

    public static function resolveType(mixed $value, mixed $context, ResolveInfo $info): ?string
    {
        return 'FooObjectType';
    }
}

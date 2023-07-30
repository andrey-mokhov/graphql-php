<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture\Native;

use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Definition\Type\UnionTypeInterface;
use GraphQL\Type\Definition as Webonyx;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class UnionType implements UnionTypeInterface, ResolveTypeAwareInterface
{
    public function getName(): string
    {
        return 'UnionType';
    }

    public function getDescription(): ?string
    {
        return 'UnionType description';
    }

    public function getTypes(): iterable
    {
        yield 'FooObjectType';
    }

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        return 'FooObjectType';
    }
}

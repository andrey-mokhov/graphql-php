<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Definition\Type\UnionTypeInterface;
use GraphQL\Type\Definition as Webonyx;

final class UserPetUnion implements UnionTypeInterface, ResolveTypeAwareInterface
{
    public function getName(): string
    {
        return 'UserPetUnion';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getTypes(): iterable
    {
        yield 'User';
        yield Pet::class;
    }

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        if ($value instanceof User) {
            return User::class;
        }

        if (is_string($value)) {
            return 'pet';
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Type\AbstractUnionType;
use GraphQL\Type\Definition as Webonyx;

final class ExampleAbstractUnionType extends AbstractUnionType implements ResolveTypeAwareInterface
{
    protected string $name = 'ExampleAbstractUnionType';

    protected iterable $types = [
        User::class,
        'pet',
    ];

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        if ($value instanceof User) {
            return 'User';
        }

        if (is_string($value)) {
            return Pet::class;
        }

        return null;
    }
}

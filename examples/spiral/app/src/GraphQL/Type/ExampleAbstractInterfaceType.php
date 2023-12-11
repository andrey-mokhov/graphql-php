<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Type\AbstractInterfaceType;
use GraphQL\Type\Definition as Webonyx;

final class ExampleAbstractInterfaceType extends AbstractInterfaceType implements ResolveTypeAwareInterface
{
    protected string $name = 'ExampleAbstractInterfaceType';

    protected iterable $fields = [
        'lastname' => 'String',
        'firstname' => [
            'type' => 'String',
            'mode' => TypeAwareInterface::IS_REQUIRED,
            'description' => 'User firstname',
        ],
    ];

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        return match (true) {
            $value instanceof ExampleAbstractObjectType => ExampleAbstractObjectType::class,
            default => null,
        };
    }
}

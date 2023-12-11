<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;
use Andi\GraphQL\Type\AbstractObjectType;
use GraphQL\Type\Definition as Webonyx;

final class ExampleAbstractObjectType extends AbstractObjectType implements ResolveFieldAwareInterface
{
    protected string $name = 'ExampleAbstractObjectType';

    protected iterable $fields = [
        'lastname' => 'String',
        'firstname' => [
            'type' => 'String',
            'mode' => TypeAwareInterface::IS_REQUIRED,
            'description' => 'User firstname',
            'resolve' => [self::class, 'getFirstname'],
        ],
    ];

    protected iterable $interfaces = [ExampleAbstractInterfaceType::class];

    private function getFirstname(User $user): string
    {
        return $user->getFirstname();
    }

    public function resolveField(mixed $value, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        /** @var User $value */
        return match ($info->fieldName) {
            'lastname' => $value->getLastname(),
            default => null,
        };
    }
}

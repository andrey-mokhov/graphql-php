<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;
use Andi\GraphQL\Type\AbstractInputObjectType;
use GraphQL\Type\Definition\StringType;

final class RegistrationRequest extends AbstractInputObjectType implements ParseValueAwareInterface
{
    protected string $name = 'RegistrationRequest';

    protected iterable $fields = [
        'lastname' => 'String',
        'firstname' => [
            'type' => StringType::class,
            'mode' => TypeAwareInterface::IS_REQUIRED,
        ],
        'middlename' => [
            'type' => 'String',
            'defaultValue' => null,
        ],
    ];

    public static function parseValue(array $values): object
    {
        $object = new \stdClass();
        $object->lastname = $values['lastname'] ?? 'Smith';
        $object->firstname = $values['firstname'];
        $object->middlename = $values['middlename'] ?? 'junior';

        return $object;
    }
}

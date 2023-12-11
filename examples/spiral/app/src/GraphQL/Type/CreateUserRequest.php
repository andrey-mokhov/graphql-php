<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;

#[InputObjectType]
final class CreateUserRequest implements ParseValueAwareInterface
{
    /**
     * @param string $lastname Фамилия
     * @param string $firstname Имя
     * @param string $middlename Отчество
     */
    public function __construct(
        #[InputObjectField] public readonly string $lastname,
        #[InputObjectField] public readonly string $firstname,
        #[InputObjectField] public readonly string $middlename,
    ) {
    }

    public static function parseValue(array $values): self
    {
        return new self($values['lastname'], $values['firstname'], $values['middlename']);
    }
}

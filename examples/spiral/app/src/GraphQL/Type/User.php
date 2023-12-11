<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use App\GraphQL\Field\UserFullName;

#[ObjectType]
class User implements UserInterface, InterfacesAwareInterface, FieldsAwareInterface
{
    public function __construct(
        private readonly string $lastname,
        private readonly string $firstname,
        #[ObjectField]
        private readonly string $middlename,
    ) {
    }

    #[ObjectField]
    public function getLastname(): string
    {
        return $this->lastname;
    }

    #[ObjectField]
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    #[ObjectField]
    public function getDisplayName(): string
    {
        return sprintf('%1$s %2$.1s. %3$s',
            $this->firstname,
            $this->middlename,
            $this->lastname,
        );
    }

    #[ObjectField(type: Pet::class)]
    public function getPet(): string
    {
        return 'Cerberus';
    }

    public function getInterfaces(): iterable
    {
        yield UserInterface::class;
        yield FullNameAwareInterface::class;
    }

    public function getFields(): iterable
    {
        yield new UserFullName();
    }
}

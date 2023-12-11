<?php

declare(strict_types=1);

namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use App\GraphQL\Type\CreateUserRequest;
use App\GraphQL\Type\User;

final class UserService
{
    #[MutationField]
    public function createUser(#[Argument] CreateUserRequest $input): User
    {
        return new User($input->lastname, $input->firstname, $input->middlename);
    }
}

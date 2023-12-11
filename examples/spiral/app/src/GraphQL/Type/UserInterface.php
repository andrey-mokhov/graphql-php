<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\InterfaceField;
use Andi\GraphQL\Attribute\InterfaceType;

#[InterfaceType]
interface UserInterface
{
    #[InterfaceField]
    public function getLastname(): string;

    #[InterfaceField]
    public function getFirstname(): string;

    #[InterfaceField]
    public function getDisplayName(): string;
}

<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\EnumType;

#[EnumType]
enum DirectionEnum: string
{
    case asc = 'asc';

    case desc = 'desc';
}

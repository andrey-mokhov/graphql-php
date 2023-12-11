<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Type\AbstractEnumType;

final class CoinSides extends AbstractEnumType
{
    protected string $name = 'CoinSides';

    protected iterable $values = [
        'heads' => true,
        'tails' => [
            'value' => false,
            'description' => 'Tails of coin',
        ],
    ];
}

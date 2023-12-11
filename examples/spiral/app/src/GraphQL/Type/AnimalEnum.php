<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\EnumValueInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;
use Andi\GraphQL\Field\EnumValue;

final class AnimalEnum implements EnumTypeInterface
{
    public const DOG = 12;
    public const CAT = 15;

    public function getName(): string
    {
        return 'Animal';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getValues(): iterable
    {
        yield new class implements EnumValueInterface {
            public function getName(): string
            {
                return 'dog';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function getDeprecationReason(): ?string
            {
                return null;
            }

            public function getValue(): mixed
            {
                // Any php-value
                return AnimalEnum::DOG;
            }
        };

        yield new EnumValue(name: 'cat', value: AnimalEnum::CAT);
    }
}

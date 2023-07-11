<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Definition\Field\EnumValueInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class EnumType implements EnumTypeInterface
{
    public function getName(): string
    {
        return 'EnumType';
    }

    public function getDescription(): ?string
    {
        return 'EnumType description';
    }

    public function getValues(): iterable
    {
        yield new class implements EnumValueInterface {

            public function getName(): string
            {
                return 'name';
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
                return 'value';
            }
        };
    }
}

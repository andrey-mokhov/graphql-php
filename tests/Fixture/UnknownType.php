<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Definition\Type\TypeInterface;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class UnknownType implements TypeInterface
{
    public function getName(): string
    {
        return 'UnknownType';
    }

    public function getDescription(): ?string
    {
        return null;
    }
}

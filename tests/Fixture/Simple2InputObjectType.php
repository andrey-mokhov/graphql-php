<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Attribute\InputObjectType;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
#[InputObjectType]
class Simple2InputObjectType
{
    #[InputObjectField]
    public string $foo;

    public string $bar;

    #[InputObjectField]
    public function setBar(string $value): void
    {
        $this->bar = $value;
    }
}

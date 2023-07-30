<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Attribute\InputObjectType;

/**
 * SimpleInputObjectType description.
 *
 * @internal
 * @psalm-internal Andi\Tests
 */
#[InputObjectType(factory: ParseValueFactory::class)]
class SimpleInputObjectType
{
    #[InputObjectField]
    protected string $foo;

    #[InputObjectField]
    public function setBar(string $qwe): self
    {
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Type\DynamicObjectTypeInterface;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
final class DynamicObjectType extends ObjectType implements DynamicObjectTypeInterface
{
    public function getName(): string
    {
        return 'DynamicObjectType';
    }

    public function getDescription(): ?string
    {
        return 'DynamicObjectType description';
    }


    public function addAdditionalField(mixed $field): static
    {
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

class Pet implements ObjectTypeInterface, DynamicObjectTypeInterface
{
    private array $additionalFields = [];

    public function getName(): string
    {
        return 'pet';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new Webonyx\FieldDefinition([
            'name' => 'nickname',
            'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
            'resolve' => static fn (string $nickname) => $nickname,
        ]);

        yield from $this->additionalFields;
    }

    public function addAdditionalField(mixed $field): static
    {
        $this->additionalFields[] = $field;

        return $this;
    }
}

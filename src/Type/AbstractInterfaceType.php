<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use Andi\GraphQL\Exception\CantResolveObjectFieldException;
use Andi\GraphQL\Field\AbstractAnonymousObjectField;
use Andi\GraphQL\Field\AbstractObjectField;
use GraphQL\Type\Definition as Webonyx;

abstract class AbstractInterfaceType extends AbstractType implements InterfaceTypeInterface, DynamicObjectTypeInterface
{
    /**
     * @template A of array{name: string, type: string, typeMode: int, description: string, deprecationReason: string, defaultValue: mixed}
     * @template F of array{name: string, type: string, typeMode: int, description: string, deprecationReason: string, arguments: array<array-key, string|A>}
     *
     * @var iterable<array-key, string|ObjectFieldInterface|Webonyx\FieldDefinition|F>
     */
    protected iterable $fields;

    protected iterable $additionalFields = [];

    public function getFields(): iterable
    {
        foreach ($this->fields ?? [] as $name => $field) {
            if ($field instanceof Webonyx\FieldDefinition || $field instanceof ObjectFieldInterface) {
                yield $field;

                continue;
            }

            if (is_string($field) || is_array($field)) {
                yield $this->getObjectField($name, $field);

                continue;
            }

            throw new CantResolveObjectFieldException(
                'Can\'t resolve ObjectField configuration: unknown field configuration',
            );
        }

        yield from $this->additionalFields;
    }

    public function addAdditionalField(mixed $field): static
    {
        $this->additionalFields[] = $field;

        return $this;
    }

    private function getObjectField(int|string $name, string|array $field): AbstractObjectField
    {
        $fieldName = $field['name'] ?? $name;

        if (! is_string($fieldName)) {
            throw new CantResolveObjectFieldException('Can\'t resolve ObjectField configuration: undefined name');
        }

        if (is_string($field)) {
            return $this->makeObjectField($fieldName, ['type' => $field]);
        }

        if (is_array($field)) {
            if (! isset($field['type']) || ! is_string($field['type'])) {
                throw new CantResolveObjectFieldException(
                    'Can\'t resolve ObjectField configuration: undefined type',
                );
            }

            return $this->makeObjectField($fieldName, $field);
        }
    }

    private function makeObjectField(string $name, array $field): AbstractObjectField
    {
        return new class($name, $field) extends AbstractAnonymousObjectField {};
    }
}

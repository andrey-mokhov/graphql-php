<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use Andi\GraphQL\Exception\CantResolveObjectFieldException;
use Andi\GraphQL\Field\AbstractAnonymousObjectField;
use Andi\GraphQL\Field\AbstractObjectField;
use GraphQL\Type\Definition as Webonyx;

/**
 * @phpstan-type ArgumentConfig array{
 *     name: string,
 *     type: string,
 *     mode: int,
 *     description?: string|null,
 *     deprecationReason?: string|null,
 *     defaultValue: mixed
 * }
 * @phpstan-type FieldConfig array{
 *     name: string,
 *     type: string,
 *     mode: int,
 *     description?: string|null,
 *     deprecationReason?: string|null,
 *     arguments: array<array-key, string|ArgumentConfig>
 * }
 */
abstract class AbstractInterfaceType extends AbstractType implements InterfaceTypeInterface, DynamicObjectTypeInterface
{
    /**
     * @var iterable<array-key, string|ObjectFieldInterface|Webonyx\FieldDefinition|FieldConfig>
     */
    protected iterable $fields;

    protected array $additionalFields = [];

    public function getFields(): iterable
    {
        /**
         * @psalm-suppress RedundantPropertyInitializationCheck
         * @psalm-suppress RedundantCondition
         * @psalm-suppress TypeDoesNotContainType
         */
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
                'Can\'t resolve ObjectField: wrong field configuration',
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
            throw new CantResolveObjectFieldException(
                'Can\'t resolve ObjectField: wrong configuration - undefined name',
            );
        }

        if (is_string($field)) {
            return $this->makeObjectField($fieldName, ['type' => $field]);
        }

        if (! isset($field['type']) || ! is_string($field['type'])) {
            throw new CantResolveObjectFieldException(
                'Can\'t resolve ObjectField: wrong configuration - undefined type',
            );
        }

        return $this->makeObjectField($fieldName, $field);
    }

    private function makeObjectField(string $name, array $field): AbstractObjectField
    {
        /** @psalm-suppress InternalClass */
        return new class($name, $field) extends AbstractAnonymousObjectField {};
    }
}

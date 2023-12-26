<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;
use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
use Andi\GraphQL\Exception\CantResolveInputObjectFieldException;
use Andi\GraphQL\Field\FieldExtractorTrait;
use Andi\GraphQL\Field\InputObjectField;
use GraphQL\Type\Definition as Webonyx;

abstract class AbstractInputObjectType extends AbstractType implements InputObjectTypeInterface
{
    use FieldExtractorTrait;

    protected iterable $fields;

    public function getFields(): iterable
    {
        foreach ($this->fields as $name => $field) {
            if ($field instanceof Webonyx\InputObjectField || $field instanceof InputObjectFieldInterface) {
                yield $field;
            } elseif (\is_string($field)) {
                yield new InputObjectField($name, $field);
            } elseif (\is_array($field)) {
                if (\is_string($name)) {
                    $field['name'] ??= $name;
                }

                yield $this->extract($field, InputObjectField::class);
            } else {
                throw new CantResolveInputObjectFieldException(
                    'Can\'t resolve InputObjectField: unknown field configuration',
                );
            }
        }
    }
}

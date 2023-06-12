<?php

declare(strict_types=1);

namespace Andi\GraphQL\WebonyxType;

use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

class ObjectType extends Webonyx\ObjectType implements DynamicObjectTypeInterface
{
    private readonly mixed $innerFields;

    private array $additionalFields = [];

    public function __construct(
        private readonly ObjectFieldResolverInterface $fieldResolver,
        array $config
    ) {
        if (isset($config['fields'])) {
            $this->innerFields = $config['fields'];
        }

        $config['fields'] = $this->extractFields(...);

        parent::__construct($config);
    }

    public function addAdditionalField(mixed $field): static
    {
        $this->additionalFields[] = $field;

        return $this;
    }

    private function extractFields(): iterable
    {
        if (isset($this->innerFields)) {
            $fields = is_callable($this->innerFields)
                ? call_user_func($this->innerFields)
                : $this->innerFields;

            if (is_iterable($fields)) {
                yield from $fields;
            }
        }

        foreach ($this->additionalFields as $field) {
            yield $this->fieldResolver->resolve($field);
        }
    }
}

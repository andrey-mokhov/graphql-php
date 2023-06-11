<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;

final class LazyWebonyxInputObjectFields
{
    public function __construct(
        private readonly FieldsAwareInterface $fields,
        private readonly InputObjectFieldResolverInterface $fieldResolver,
    ) {
    }

    public function __invoke(): iterable
    {
        foreach ($this->fields->getFields() as $field) {
            yield $this->fieldResolver->resolve($field);
        }
    }
}

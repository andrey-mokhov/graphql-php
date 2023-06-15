<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;

final class LazyObjectFields
{
    public function __construct(
        private readonly FieldsAwareInterface $fields,
        private readonly ObjectFieldResolverInterface $fieldResolver,
    ) {
    }

    public function __invoke(): iterable
    {
        foreach ($this->fields->getFields() as $field) {
            yield $this->fieldResolver->resolve($field);
        }
    }
}

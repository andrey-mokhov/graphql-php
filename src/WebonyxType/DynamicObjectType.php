<?php

declare(strict_types=1);

namespace Andi\GraphQL\WebonyxType;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

class DynamicObjectType extends Webonyx\ObjectType implements DynamicObjectTypeInterface
{
    public function __construct(
        protected readonly DynamicObjectTypeInterface $dynamicType,
        array $config,
    ) {
        parent::__construct($config);
    }

    public function addAdditionalField(ObjectFieldInterface $field): static
    {
        $this->dynamicType->addAdditionalField($field);

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Attribute\InputObjectField;
use ReflectionMethod;

trait InputObjectFieldNameTrait
{
    private function getInputObjectFieldName(ReflectionMethod $method, ?InputObjectField $attribute): string
    {
        if (null !== $attribute?->name) {
            return $attribute->name;
        }

        $name = $method->getName();

        if (str_starts_with($name, 'set')) {
            $name = substr($name, 3);
        }

        return lcfirst($name);
    }
}

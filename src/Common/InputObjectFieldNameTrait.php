<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Attribute\InputObjectField;

trait InputObjectFieldNameTrait
{
    private function getInputObjectFieldName(\ReflectionMethod $method, ?InputObjectField $attribute): string
    {
        if ($attribute?->name) {
            \assert($attribute->name !== null);
            return $attribute->name;
        }

        $name = $method->getName();

        if (\str_starts_with($name, 'set')) {
            $name = \substr($name, 3);
        }

        return \lcfirst($name);
    }
}

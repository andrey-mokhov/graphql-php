<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Attribute;
use ReflectionClass;

trait DefinitionAwareTrait
{
    private function getTypeName(ReflectionClass $class, Attribute\AbstractType|null $attribute): string
    {
        return $attribute?->name ?? $class->getShortName();
    }

    /**
     * @param ReflectionClass $class
     * @param Attribute\AbstractType|null $attribute
     *
     * @return string|null
     *
     * @todo Extract description from annotation when attribute is not set
     */
    private function getTypeDescription(ReflectionClass $class, ?Attribute\AbstractType $attribute): ?string
    {
        return $attribute?->description;
    }
}

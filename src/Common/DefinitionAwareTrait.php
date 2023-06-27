<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Attribute;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;

trait DefinitionAwareTrait
{
    private function getTypeName(ReflectionClass $class, Attribute\AbstractType|null $attribute): string
    {
        return $attribute?->name ?? $class->getShortName();
    }

    private function getTypeDescription(ReflectionClass $class, ?Attribute\AbstractType $attribute): ?string
    {
        if ($attribute?->description) {
            return $attribute->description;
        }

        if ($docComment = $class->getDocComment()) {
            return DocBlockFactory::createInstance()->create($docComment)->getSummary() ?: null;
        }

        return null;
    }
}

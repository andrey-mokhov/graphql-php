<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition as Webonyx;
use ReflectionClass;

final class InterfaceResolveType
{
    public function __construct(
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function __invoke(mixed $value, mixed $context, ResolveInfo $info): ?Webonyx\ObjectType
    {
        if (! is_object($value)) {
            return null;
        }

        return $this->getObjectType(new ReflectionClass($value));
    }

    private function getObjectType(ReflectionClass $class): ?Webonyx\ObjectType
    {
        $name = $class->getName();

        if ($this->typeRegistry->has($name)) {
            $type = $this->typeRegistry->get($name);

            return $type instanceof Webonyx\ObjectType
                ? $type
                : null;
        }

        if ($parent = $class->getParentClass()) {
            return $this->getObjectType($parent);
        }

        return null;
    }
}

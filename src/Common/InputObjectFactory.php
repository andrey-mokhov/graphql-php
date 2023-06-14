<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Attribute\InputObjectField;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spiral\Core\ResolverInterface;

final class InputObjectFactory
{
    /**
     * @var array<string, ReflectionMethod|ReflectionProperty>
     */
    private readonly array $map;

    public function __construct(
        private readonly ReflectionClass $class,
        private readonly ResolverInterface $resolver,
    ) {
        $map = [];
        foreach ($this->class->getProperties() as $property) {
            if ($attributes = $property->getAttributes(InputObjectField::class)) {
                $attribute = $attributes[0]->newInstance();

                $name = $this->getFieldNameByProperty($property, $attribute);

                $map[$name] = $property;
            }
        }

        foreach ($this->class->getMethods() as $method) {
            if ($attributes = $method->getAttributes(InputObjectField::class)) {
                $attribute = $attributes[0]->newInstance();

                $name = $this->getFieldNameByMethod($method, $attribute);

                $map[$name] = $method;
            }
        }

        $this->map = $map;
    }

    public function __invoke(array $values): mixed
    {
        $instance = $this->class->newInstanceWithoutConstructor();

        foreach ($values as $name => $value) {
            if (isset($this->map[$name])) {
                $reflection = $this->map[$name];

                if ($reflection instanceof ReflectionProperty) {
                    $reflection->setValue($instance, $value);
                } elseif ($reflection instanceof ReflectionMethod) {
                    $reflection->invokeArgs($instance,$this->resolver->resolveArguments($reflection, [$value]));
                }
            }
        }

        return $instance;
    }

    private function getFieldNameByProperty(ReflectionProperty $property, InputObjectField $attribute): string
    {
        return $attribute->name ?? $property->getName();
    }

    private function getFieldNameByMethod(ReflectionMethod $method, InputObjectField $attribute): string
    {
        if (null !== $attribute->name) {
            return $attribute->name;
        }

        $name = $method->getName();

        if (str_starts_with($name, 'set')) {
            $name = substr($name, 3);
        }

        return lcfirst($name);
    }
}

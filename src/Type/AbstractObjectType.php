<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use Andi\GraphQL\Definition\Field\ComplexityAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Exception\CantResolveObjectFieldException;
use Andi\GraphQL\Field\AbstractAnonymousObjectField;
use Andi\GraphQL\Field\AbstractObjectField;
use Andi\GraphQL\Field\AnonymousComplexityAwareTrait;
use Andi\GraphQL\Field\AnonymousResolveAwareTrait;
use GraphQL\Type\Definition as Webonyx;

abstract class AbstractObjectType extends AbstractType implements
    ObjectTypeInterface,
    InterfacesAwareInterface,
    DynamicObjectTypeInterface
{
    /**
     * @template A of array{name: string, type: string, typeMode: int, description: string, deprecationReason: string, defaultValue: mixed}
     * @template F of array{name: string, type:string, typeMode: int, description: string, deprecationReason: string, resolve: callable, complexity: callable, arguments: array<array-key, string|A>}
     *
     * @var iterable<array-key, string|ObjectFieldInterface|Webonyx\FieldDefinition|F>
     */
    protected iterable $fields;

    protected iterable $interfaces;

    protected iterable $additionalFields = [];

    public function getFields(): iterable
    {
        foreach ($this->fields ?? [] as $name => $field) {
            if ($field instanceof Webonyx\FieldDefinition || $field instanceof ObjectFieldInterface) {
                yield $field;

                continue;
            }

            if (is_string($field) || is_array($field)) {
                yield $this->getObjectField($name, $field);

                continue;
            }

            throw new CantResolveObjectFieldException(
                'Can\'t resolve ObjectField configuration: unknown field configuration',
            );
        }

        yield from $this->additionalFields;
    }

    public function getInterfaces(): iterable
    {
        yield from $this->interfaces ?? [];
    }

    public function addAdditionalField(mixed $field): static
    {
        $this->additionalFields[] = $field;

        return $this;
    }

    private function getObjectField(int|string $name, string|array $field): AbstractObjectField
    {
        $fieldName = $field['name'] ?? $name;

        if (! is_string($fieldName)) {
            throw new CantResolveObjectFieldException('Can\'t resolve ObjectField configuration: undefined name');
        }

        if (is_string($field)) {
            return $this->makeObjectField($fieldName, ['type' => $field]);
        }

        if (is_array($field)) {
            if (! isset($field['type']) || ! is_string($field['type'])) {
                throw new CantResolveObjectFieldException(
                    'Can\'t resolve ObjectField configuration: undefined type',
                );
            }

            if (isset($field['resolve'], $field['complexity'])) {
                return $this->makeObjectFieldWithBoth($fieldName, $field);
            }

            if (isset($field['resolve'])) {
                return $this->makeObjectFieldWithResolve($fieldName, $field);
            }

            if (isset($field['complexity'])) {
                return $this->makeObjectFieldWithComplexity($fieldName, $field);
            }

            return $this->makeObjectField($fieldName, $field);
        }
    }

    private function makeObjectField(string $name, array $field): AbstractObjectField
    {
        return new class($name, $field) extends AbstractAnonymousObjectField {};
    }

    private function makeObjectFieldWithResolve(string $name, array $field): AbstractObjectField
    {
        $resolve = $this->makeClosure($field['resolve']);

        return new class($name, $field, $resolve) extends AbstractAnonymousObjectField implements
            ResolveAwareInterface
        {
            use AnonymousResolveAwareTrait;
        };
    }

    private function makeObjectFieldWithComplexity(string $name, array $field): AbstractObjectField
    {
        $complexity = $this->makeClosure($field['complexity']);

        return new class($name, $field, null, $complexity) extends AbstractAnonymousObjectField implements
            ComplexityAwareInterface
        {
            use AnonymousComplexityAwareTrait;
        };
    }

    private function makeObjectFieldWithBoth(string $name, array $field): AbstractObjectField
    {
        $resolve = $this->makeClosure($field['resolve']);
        $complexity = $this->makeClosure($field['complexity']);

        return new class($name, $field, $resolve, $complexity) extends AbstractAnonymousObjectField implements
            ResolveAwareInterface,
            ComplexityAwareInterface
        {
            use AnonymousResolveAwareTrait;
            use AnonymousComplexityAwareTrait;
        };
    }

    private function makeClosure($callable): \Closure
    {
        if ($callable instanceof \Closure) {
            return $callable;
        }

        if (is_array($callable)) {
            if (is_callable($callable)) {
                return \Closure::fromCallable($callable);
            }

            if (! isset($callable[0], $callable[1])) {
                throw new CantResolveObjectFieldException(
                    'Can\'t resolve ObjectField configuration: resolve must be callable',
                );
            }

            try {
                $method = new \ReflectionMethod($callable[0], $callable[1]);
            } catch (\ReflectionException $exception) {
                throw new CantResolveObjectFieldException(
                    'Can\'t resolve ObjectField configuration: resolve must be callable',
                    $exception->getCode(),
                    $exception,
                );
            }

            return $method->getClosure($this);
        }

        if (is_string($callable)) {
            try {
                $method = str_contains($callable, '::')
                    ? new \ReflectionMethod(...explode('::', $callable, 2))
                    : new \ReflectionMethod($this, $callable);
            } catch (\ReflectionException $exception) {
                throw new CantResolveObjectFieldException(
                    'Can\'t resolve ObjectField configuration: resolve must be callable',
                    $exception->getCode(),
                    $exception,
                );
            }

            return $method->getClosure($this);
        }

        throw new CantResolveObjectFieldException(
            'Can\'t resolve ObjectField configuration: resolve must be callable',
        );
    }
}

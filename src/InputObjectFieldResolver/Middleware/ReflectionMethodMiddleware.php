<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Common\LazyWebonyxNodeType;
use Andi\GraphQL\Common\LazyWebonyxTypeByReflectionParameter;
use Andi\GraphQL\Common\LazyWebonyxTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionMethod;
use ReflectionParameter;
use Spiral\Attributes\ReaderInterface;

final class ReflectionMethodMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 3072;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function process(mixed $field, InputObjectFieldResolverInterface $fieldResolver): Webonyx\InputObjectField
    {
        if (! $field instanceof ReflectionMethod) {
            return $fieldResolver->resolve($field);
        }

        $attribute = $this->reader->firstFunctionMetadata($field, InputObjectField::class);

        $config = [
            'name'              => $this->getFieldName($field, $attribute),
            'description'       => $this->getFieldDescription($field, $attribute),
            'type'              => $this->getFieldType($field, $attribute),
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        return new Webonyx\InputObjectField($config);
    }

    private function getFieldName(ReflectionMethod $method, ?InputObjectField $attribute): ?string
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

    /**
     * @param ReflectionMethod $method
     * @param InputObjectField|null $attribute
     *
     * @return string|null
     *
     * @todo Extract description from annotation when attribute is not set.
     */
    private function getFieldDescription(ReflectionMethod $method, ?InputObjectField $attribute): ?string
    {
        return $attribute?->description;
    }

    private function getFieldType(ReflectionMethod $method, ?InputObjectField $attribute): callable
    {
        if (null !== $attribute?->type) {
            return new LazyWebonyxNodeType($attribute->type, $this->typeRegistry);
        }

        $parameters = $method->getParameters();

        if (1 !== count($parameters)) {
            throw new CantResolveGraphQLTypeException(sprintf(
                'Can\'t resolve GraphQL type "%s" for field "%s". Method must have a single parameter.',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
            ));
        }

        $parameter = $parameters[0];

        return new LazyWebonyxTypeByReflectionParameter($parameter, $this->typeRegistry);
    }

    /**
     * @param ReflectionMethod $method
     * @param InputObjectField|null $attribute
     *
     * @return string|null
     *
     * @todo Extract deprecation reason from annotation when attribute is not set
     */
    private function getFieldDeprecationReason(ReflectionMethod $method, ?InputObjectField $attribute): ?string
    {
        return $attribute?->deprecationReason;
    }

    private function hasDefaultValue(ReflectionParameter $parameter, ?InputObjectField $attribute): bool
    {
        return $attribute?->hasDefaultValue() || $parameter->isDefaultValueAvailable();
    }

    private function getArgumentDefaultValue(ReflectionParameter $parameter, ?InputObjectField $attribute): mixed
    {
        if ($attribute?->hasDefaultValue()) {
            return  $attribute->defaultValue;
        }

        return $parameter->getDefaultValue();
    }
}

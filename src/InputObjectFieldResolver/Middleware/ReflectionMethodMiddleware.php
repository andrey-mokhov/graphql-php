<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Common\InputObjectFieldNameTrait;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionParameter;
use Andi\GraphQL\Common\ReflectionMethodWithAttribute;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;
use ReflectionParameter;
use Spiral\Attributes\ReaderInterface;

final class ReflectionMethodMiddleware implements MiddlewareInterface
{
    use InputObjectFieldNameTrait;

    public const PRIORITY = 3072;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function process(mixed $field, InputObjectFieldResolverInterface $fieldResolver): Webonyx\InputObjectField
    {
        if (! $field instanceof ReflectionMethodWithAttribute) {
            return $fieldResolver->resolve($field);
        }

        if (! $field->attribute instanceof InputObjectField) {
            return $fieldResolver->resolve($field);
        }

        $config = [
            'name' => $this->getInputObjectFieldName($field->method, $field->attribute),
            'description' => $this->getFieldDescription($field->method, $field->attribute),
            'type' => $this->getFieldType($field->method, $field->attribute),
            'deprecationReason' => $this->getFieldDeprecationReason($field->method, $field->attribute),
        ];

        $parameter = $field->method->getParameters()[0];

        if ($this->hasDefaultValue($parameter, $field->attribute)) {
            $config['defaultValue'] = $this->getDefaultValue($parameter, $field->attribute);
        }

        return new Webonyx\InputObjectField($config);
    }

    /**
     * @param ReflectionMethod $method
     * @param InputObjectField|null $attribute
     *
     * @return string|null
     */
    private function getFieldDescription(ReflectionMethod $method, ?InputObjectField $attribute): ?string
    {
        if ($attribute?->description) {
            return $attribute->description;
        }

        if ($docComment = $method->getDocComment()) {
            return DocBlockFactory::createInstance()->create($docComment)->getSummary() ?: null;
        }

        return null;
    }

    private function getFieldType(ReflectionMethod $method, ?InputObjectField $attribute): callable
    {
        if ($attribute?->type) {
            return new LazyParserType($attribute->type, $attribute->mode ?? 0, $this->typeRegistry);
        }

        $parameters = $method->getParameters();

        if (1 !== \count($parameters)) {
            throw new CantResolveGraphQLTypeException(\sprintf(
                'Can\'t resolve GraphQL type "%s" for field "%s". Method must have a single parameter.',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
            ));
        }

        $parameter = $parameters[0];

        if (! $parameter->hasType()) {
            throw new CantResolveGraphQLTypeException(\sprintf(
                'Can\'t resolve GraphQL type "%s" for field "%s". Parameter has no type.',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
            ));
        }

        return new LazyTypeByReflectionParameter($parameter, $this->typeRegistry);
    }

    /**
     * @param ReflectionMethod $method
     * @param InputObjectField|null $attribute
     *
     * @return string|null
     */
    private function getFieldDeprecationReason(ReflectionMethod $method, ?InputObjectField $attribute): ?string
    {
        if ($attribute?->deprecationReason) {
            return $attribute->deprecationReason;
        }

        if ($docComment = $method->getDocComment()) {
            $docBlock = DocBlockFactory::createInstance()->create($docComment);
            foreach ($docBlock->getTags() as $tag) {
                if ($tag instanceof Deprecated) {
                    return (string) $tag->getDescription() ?: null;
                }
            }
        }

        return null;
    }

    private function hasDefaultValue(ReflectionParameter $parameter, ?InputObjectField $attribute): bool
    {
        return $attribute?->hasDefaultValue() || $parameter->isDefaultValueAvailable();
    }

    private function getDefaultValue(ReflectionParameter $parameter, ?InputObjectField $attribute): mixed
    {
        if ($attribute?->hasDefaultValue()) {
            return $attribute->defaultValue;
        }

        return $parameter->getDefaultValue();
    }
}

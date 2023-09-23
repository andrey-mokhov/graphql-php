<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionProperty;
use Spiral\Attributes\ReaderInterface;

final class ReflectionPropertyMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 4096;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function process(mixed $field, InputObjectFieldResolverInterface $fieldResolver): Webonyx\InputObjectField
    {
        if (! $field instanceof ReflectionProperty) {
            return $fieldResolver->resolve($field);
        }

        $attribute = $this->reader->firstPropertyMetadata($field, InputObjectField::class);

        $config = [
            'name'              => $this->getFieldName($field, $attribute),
            'description'       => $this->getFieldDescription($field, $attribute),
            'type'              => $this->getFieldType($field, $attribute),
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        if ($this->hasDefaultValue($field, $attribute)) {
            $config['defaultValue'] = $this->getDefaultValue($field, $attribute);
        }

        return new Webonyx\InputObjectField($config);
    }

    private function getFieldName(ReflectionProperty $property, ?InputObjectField $attribute): string
    {
        return $attribute?->name
            ?? $property->getName();
    }

    private function getFieldDescription(ReflectionProperty $property, ?InputObjectField $attribute): ?string
    {
        if ($attribute?->description) {
            return $attribute->description;
        }

        if ($property->isPromoted()) {
            if ($docComment = $property->getDeclaringClass()->getConstructor()->getDocComment()) {
                $docBlock = DocBlockFactory::createInstance(['psalm-param' => Param::class])->create($docComment);
                foreach ($docBlock->getTags() as $tag) {
                    if ($tag instanceof Param && $tag->getVariableName() === $property->getName()) {
                        return (string) $tag->getDescription() ?: null;
                    }
                }
            }
        } elseif ($docComment = $property->getDocComment()) {
            return DocBlockFactory::createInstance()->create($docComment)->getSummary() ?: null;
        }

        return null;
    }

    private function getFieldType(ReflectionProperty $property, ?InputObjectField $attribute): callable
    {
        if ($attribute?->type) {
            return new LazyParserType($attribute->type, $this->typeRegistry);
        }

        if (! $property->hasType()) {
            throw new CantResolveGraphQLTypeException(sprintf(
                'Can\'t resolve GraphQL type for field "%s"',
                $property->getName()
            ));
        }

        return new LazyTypeByReflectionType(
            $property->getType(),
            $this->typeRegistry,
            $property->getDeclaringClass()->getName(),
        );
    }

    /**
     * @param ReflectionProperty $property
     * @param InputObjectField|null $attribute
     *
     * @return string|null
     */
    private function getFieldDeprecationReason(ReflectionProperty $property, ?InputObjectField $attribute): ?string
    {
        if ($attribute?->deprecationReason) {
            return $attribute->deprecationReason;
        }

        if ($docComment = $property->getDocComment()) {
            $docBlock = DocBlockFactory::createInstance(['property-deprecated' => Deprecated::class])
                ->create($docComment);
            foreach ($docBlock->getTags() as $tag) {
                if ($tag instanceof Deprecated) {
                    return (string) $tag->getDescription() ?: null;
                }
            }
        }

        return null;
    }

    private function hasDefaultValue(ReflectionProperty $property, ?InputObjectField $attribute): bool
    {
        return $attribute?->hasDefaultValue() || $property->hasDefaultValue();
    }

    private function getDefaultValue(ReflectionProperty $property, ?InputObjectField $attribute): mixed
    {
        if ($attribute?->hasDefaultValue()) {
            return $attribute->defaultValue;
        }

        return $property->getDefaultValue();
    }
}

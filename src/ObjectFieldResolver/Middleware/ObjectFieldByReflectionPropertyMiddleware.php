<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionProperty;
use Spiral\Attributes\ReaderInterface;

final class ObjectFieldByReflectionPropertyMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 3072;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function process(mixed $field, ObjectFieldResolverInterface $fieldResolver): Webonyx\FieldDefinition
    {
        if (! $field instanceof ReflectionProperty) {
            return $fieldResolver->resolve($field);
        }

        $attribute = $this->reader->firstPropertyMetadata($field, ObjectField::class);

        $config = [
            'name' => $this->getName($field, $attribute),
            'description' => $this->getFieldDescription($field, $attribute),
            'type' => $this->getFieldType($field, $attribute),
            'resolve' => $this->getFieldResolver($field),
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        return new Webonyx\FieldDefinition($config);
    }

    private function getName(ReflectionProperty $property, ?ObjectField $attribute): string
    {
        return $attribute?->name
            ?? $property->getName();
    }

    private function getFieldDescription(ReflectionProperty $property, ?ObjectField $attribute): ?string
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

    private function getFieldType(ReflectionProperty $property, ?ObjectField $attribute): callable
    {
        if ($attribute?->type) {
            return new LazyParserType($attribute->type, $attribute->mode ?? 0, $this->typeRegistry);
        }

        if (! $property->hasType()) {
            throw new CantResolveGraphQLTypeException(\sprintf(
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

    private function getFieldResolver(ReflectionProperty $property): callable
    {
        return static function ($object) use ($property): mixed {
            return $property->getValue($object);
        };
    }

    private function getFieldDeprecationReason(ReflectionProperty $property, ?ObjectField $attribute): ?string
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
}

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
            'name'              => $this->getName($field, $attribute),
            'description'       => $this->getFieldDescription($field, $attribute),
            'type'              => $this->getFieldType($field, $attribute),
            'resolve'           => $this->getFieldResolver($field),
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        return new Webonyx\FieldDefinition($config);
    }

    private function getName(ReflectionProperty $property, ?ObjectField $attribute): string
    {
        return $attribute?->name
            ?? $property->getName();
    }

    /**
     * @param ReflectionProperty $property
     * @param ObjectField|null $attribute
     *
     * @return string|null
     *
     * @todo Extract description from annotation when attribute is not set.
     */
    private function getFieldDescription(ReflectionProperty $property, ?ObjectField $attribute): ?string
    {
        return $attribute?->description;
    }

    private function getFieldType(ReflectionProperty $property, ?ObjectField $attribute): callable
    {
        if (null !== $attribute?->type) {
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

    private function getFieldResolver(ReflectionProperty $property): callable
    {
        return static function ($object) use ($property): mixed {
            return $property->getValue($object);
        };
    }

    /**
     * @param ReflectionProperty $property
     * @param ObjectField|null $attribute
     *
     * @return string|null
     *
     * @todo Extract deprecation reason from annotation when attribute is not set
     */
    private function getFieldDeprecationReason(ReflectionProperty $property, ?ObjectField $attribute): ?string
    {
        return $attribute?->deprecationReason;
    }
}

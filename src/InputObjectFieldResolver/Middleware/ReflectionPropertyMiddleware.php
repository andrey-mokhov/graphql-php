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
            'name'              => $attribute?->name ?? $field->getName(),
            'description'       => $this->getFieldDescription($field, $attribute),
            'type'              => $this->getFieldType($field, $attribute),
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        if ($this->hasDefaultValue($field, $attribute)) {
            $config['defaultValue'] = $this->getDefaultValue($field, $attribute);
        }

        return new Webonyx\InputObjectField($config);
    }

    /**
     * @param ReflectionProperty $property
     * @param InputObjectField|null $attribute
     *
     * @return string|null
     *
     * @todo Extract description from annotation when attribute is not set.
     */
    private function getFieldDescription(ReflectionProperty $property, ?InputObjectField $attribute): ?string
    {
        return $attribute?->description;
    }

    private function getFieldType(ReflectionProperty $property, ?InputObjectField $attribute): callable
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

    /**
     * @param ReflectionProperty $property
     * @param InputObjectField|null $attribute
     *
     * @return string|null
     *
     * @todo Extract deprecation reason from annotation when attribute is not set
     */
    private function getFieldDeprecationReason(ReflectionProperty $property, ?InputObjectField $attribute): ?string
    {
        return $attribute?->deprecationReason;
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

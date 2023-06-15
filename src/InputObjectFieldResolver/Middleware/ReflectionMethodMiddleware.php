<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Common\InputObjectFieldNameTrait;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionParameter;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
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
        if (! $field instanceof ReflectionMethod) {
            return $fieldResolver->resolve($field);
        }

        $attribute = $this->reader->firstFunctionMetadata($field, InputObjectField::class);

        $config = [
            'name'              => $this->getInputObjectFieldName($field, $attribute),
            'description'       => $this->getFieldDescription($field, $attribute),
            'type'              => $this->getFieldType($field, $attribute),
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        $parameter = $field->getParameters()[0];

        if ($this->hasDefaultValue($parameter, $attribute)) {
            $config['defaultValue'] = $this->getDefaultValue($parameter, $attribute);
        }

        return new Webonyx\InputObjectField($config);
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
            return new LazyParserType($attribute->type, $this->typeRegistry);
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

        return new LazyTypeByReflectionParameter($parameter, $this->typeRegistry);
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

    private function getDefaultValue(ReflectionParameter $parameter, ?InputObjectField $attribute): mixed
    {
        if ($attribute?->hasDefaultValue()) {
            return $attribute->defaultValue;
        }

        return $parameter->getDefaultValue();
    }
}

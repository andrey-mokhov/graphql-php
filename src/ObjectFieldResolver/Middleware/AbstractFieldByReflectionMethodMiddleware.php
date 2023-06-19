<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;

abstract class AbstractFieldByReflectionMethodMiddleware implements MiddlewareInterface
{
    /**
     * @var class-string
     */
    protected string $targetAttribute;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly ArgumentResolverInterface $argumentResolver,
    ) {
    }

    public function process(mixed $field, ObjectFieldResolverInterface $fieldResolver): Webonyx\FieldDefinition
    {
        if (! $field instanceof ReflectionMethod) {
            return $fieldResolver->resolve($field);
        }

        $attribute = $this->reader->firstFunctionMetadata($field, $this->targetAttribute);

        if (null === $attribute) {
            return $fieldResolver->resolve($field);
        }

        $config = [
            'name'              => $this->getFieldName($field, $attribute),
            'description'       => $this->getFieldDescription($field, $attribute),
            'type'              => $this->getFieldType($field, $attribute),
            'args'              => $this->getFieldArguments($field),
            'resolve'           => $this->getFieldResolver($field),
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        return new Webonyx\FieldDefinition($config);
    }

    private function getFieldName(ReflectionMethod $method, AbstractField $attribute): ?string
    {
        if (null !== $attribute->name) {
            return $attribute->name;
        }

        $name = $method->getName();

        if (str_starts_with($name, 'get')) {
            $name = substr($name, 3);
        }

        return lcfirst($name);
    }

    /**
     * @param ReflectionMethod $method
     * @param ObjectField $attribute
     *
     * @return string|null
     *
     * @todo Extract description from annotation when attribute is not set.
     */
    private function getFieldDescription(ReflectionMethod $method, AbstractField $attribute): ?string
    {
        return $attribute->description;
    }

    private function getFieldType(ReflectionMethod $method, AbstractField $attribute): callable
    {
        if (null !== $attribute->type) {
            return new LazyParserType($attribute->type, $this->typeRegistry);
        }

        if (! $method->hasReturnType()) {
            throw new CantResolveGraphQLTypeException(sprintf(
                'Can\'t resolve GraphQL type for field "%s"',
                $method->getName()
            ));
        }

        return new LazyTypeByReflectionType(
            $method->getReturnType(),
            $this->typeRegistry,
            $method->getDeclaringClass()->getName(),
        );
    }

    private function getFieldArguments(ReflectionMethod $method): iterable
    {
        foreach ($method->getParameters() as $parameter) {
            if (null !== $this->reader->firstParameterMetadata($parameter, Argument::class)) {
                yield $this->argumentResolver->resolve($parameter);
            }
        }
    }

    abstract protected function getFieldResolver(ReflectionMethod $method): callable;

    /**
     * @param ReflectionMethod $method
     * @param ObjectField $attribute
     *
     * @return string|null
     *
     * @todo Extract deprecation reason from annotation when attribute is not set
     */
    private function getFieldDeprecationReason(ReflectionMethod $method, AbstractField $attribute): ?string
    {
        return $attribute->deprecationReason;
    }
}
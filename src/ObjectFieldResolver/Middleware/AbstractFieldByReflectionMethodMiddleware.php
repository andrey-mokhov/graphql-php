<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;

/**
 * @see Webonyx\FieldDefinition
 *
 * @phpstan-import-type FieldDefinitionConfig from Webonyx\FieldDefinition
 */
abstract class AbstractFieldByReflectionMethodMiddleware implements MiddlewareInterface
{
    /**
     * @var class-string<AbstractField>
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
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        return $this->buildField($config, $field);
    }

    private function getFieldName(ReflectionMethod $method, AbstractField $attribute): ?string
    {
        if ($attribute->name) {
            return $attribute->name;
        }

        $name = $method->getName();

        if (str_starts_with($name, 'get')) {
            $name = substr($name, 3);
        }

        return lcfirst($name);
    }

    private function getFieldDescription(ReflectionMethod $method, AbstractField $attribute): ?string
    {
        if ($attribute->description) {
            return $attribute->description;
        }

        if ($docComment = $method->getDocComment()) {
            return DocBlockFactory::createInstance()->create($docComment)->getSummary() ?: null;
        }

        return null;

    }

    private function getFieldType(ReflectionMethod $method, AbstractField $attribute): callable
    {
        if ($attribute->type) {
            return new LazyParserType($attribute->type, $attribute->mode ?? 0, $this->typeRegistry);
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

    protected function getFieldArguments(ReflectionMethod $method): \Generator
    {
        $map = [];
        foreach ($method->getParameters() as $parameter) {
            if (null !== $this->reader->firstParameterMetadata($parameter, Argument::class)) {
                $argument = $this->argumentResolver->resolve($parameter);
                $map[$argument['name']] = $parameter->getName();

                yield $argument;
            }
        }

        return $map;
    }

    /**
     * @param FieldDefinitionConfig $config
     * @param ReflectionMethod $method
     *
     * @return Webonyx\FieldDefinition
     */
    abstract protected function buildField(array $config, ReflectionMethod $method): Webonyx\FieldDefinition;

    private function getFieldDeprecationReason(ReflectionMethod $method, AbstractField $attribute): ?string
    {
        if ($attribute->deprecationReason) {
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
}

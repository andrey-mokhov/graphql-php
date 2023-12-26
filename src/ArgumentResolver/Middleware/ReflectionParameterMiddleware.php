<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Common\LazyTypeByReflectionParameter;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeRegistryInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionParameter;
use Spiral\Attributes\ReaderInterface;

final class ReflectionParameterMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 3072;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function process(mixed $argument, ArgumentResolverInterface $argumentResolver): array
    {
        if (! $argument instanceof ReflectionParameter) {
            return $argumentResolver->resolve($argument);
        }

        $attribute = $this->reader->firstParameterMetadata($argument, Argument::class);

        $config = [
            'name' => $this->getArgumentName($argument, $attribute),
            'description' => $this->getArgumentDescription($argument, $attribute),
            'type' => $this->getArgumentType($argument, $attribute),
            'deprecationReason' => $this->getArgumentDeprecationReason($argument, $attribute),
        ];

        if ($this->hasDefaultValue($argument, $attribute)) {
            $config['defaultValue'] = $this->getArgumentDefaultValue($argument, $attribute);
        }

        return $config;
    }

    private function getArgumentName(ReflectionParameter $parameter, ?Argument $attribute): string
    {
        return $attribute?->name
            ?? $parameter->getName();
    }

    /**
     * @param ReflectionParameter $parameter
     * @param Argument|null $attribute
     *
     * @return string|null
     */
    private function getArgumentDescription(ReflectionParameter $parameter, ?Argument $attribute): ?string
    {
        if ($attribute?->description) {
            return $attribute->description;
        }

        if ($docComment = $parameter->getDeclaringFunction()->getDocComment()) {
            $docBlock = DocBlockFactory::createInstance(['psalm-param' => Param::class])->create($docComment);
            foreach ($docBlock->getTags() as $tag) {
                if ($tag instanceof Param && $tag->getVariableName() === $parameter->getName()) {
                    return (string) $tag->getDescription() ?: null;
                }
            }
        }

        return null;
    }

    private function getArgumentType(ReflectionParameter $parameter, ?Argument $attribute): callable
    {
        if ($attribute?->type) {
            return new LazyParserType($attribute->type, $attribute->mode ?? 0, $this->typeRegistry);
        }

        if (! $parameter->hasType()) {
            throw new CantResolveGraphQLTypeException(\sprintf(
                'Can\'t resolve GraphQL type for argument "%s"',
                $parameter->getName()
            ));
        }

        return new LazyTypeByReflectionParameter($parameter, $this->typeRegistry);
    }

    /**
     * @param ReflectionParameter $parameter
     * @param Argument|null $attribute
     *
     * @return string|null
     */
    private function getArgumentDeprecationReason(ReflectionParameter $parameter, ?Argument $attribute): ?string
    {
        return $attribute?->deprecationReason;
    }

    private function hasDefaultValue(ReflectionParameter $parameter, ?Argument $attribute): bool
    {
        return $attribute?->hasDefaultValue() || $parameter->isDefaultValueAvailable();
    }

    private function getArgumentDefaultValue(ReflectionParameter $parameter, ?Argument $attribute): mixed
    {
        if ($attribute?->hasDefaultValue()) {
            return $attribute->defaultValue;
        }

        return $parameter->getDefaultValue();
    }
}

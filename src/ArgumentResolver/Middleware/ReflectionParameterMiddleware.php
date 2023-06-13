<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Common\LazyWebonyxNodeType;
use Andi\GraphQL\Common\LazyWebonyxReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeRegistryInterface;
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
            'name'              => $this->getArgumentName($argument, $attribute),
            'description'       => $this->getArgumentDescription($argument, $attribute),
            'type'              => $this->getArgumentType($argument, $attribute),
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
     *
     * @todo Extract description from annotation when attribute is not set
     */
    private function getArgumentDescription(ReflectionParameter $parameter, ?Argument $attribute): ?string
    {
        return $attribute?->description;
    }

    private function getArgumentType(ReflectionParameter $parameter, ?Argument $attribute): callable
    {
        if (null !== $attribute?->type) {
            return new LazyWebonyxNodeType($attribute->type, $this->typeRegistry);
        }

        if (! $parameter->hasType()) {
            throw new CantResolveGraphQLTypeException(sprintf(
                'Can\'t resolve GraphQL type for argument "%s"',
                $parameter->getName()
            ));
        }

        return new LazyWebonyxReflectionType(
            $parameter->getType(),
            $this->typeRegistry,
            $parameter->getDeclaringClass()->getName(),
        );
    }

    /**
     * @param ReflectionParameter $parameter
     * @param Argument|null $attribute
     *
     * @return string|null
     *
     * @todo Extract deprecation reason from annotation when attribute is not set
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
            return  $attribute->defaultValue;
        }

        return $parameter->getDefaultValue();
    }
}

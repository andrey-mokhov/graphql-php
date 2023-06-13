<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Attribute;
use Andi\GraphQL\Common\LazyWebonyxInputObjectFields;
use Andi\GraphQL\Common\LazyWebonyxObjectFields;
use Andi\GraphQL\Common\LazyWebonyxTypeIterator;
use Andi\GraphQL\Definition\Field\ParseValueAwareInterface;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\GraphQL\WebonyxType\InputObjectType;
use Andi\GraphQL\WebonyxType\ObjectType;
use GraphQL\Type\Definition as Webonyx;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Spiral\Attributes\ReaderInterface;

final class AttributedGraphQLTypeMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly ObjectFieldResolverInterface $objectFieldResolver,
        private readonly InputObjectFieldResolverInterface $inputObjectFieldResolver,
    ) {
    }

    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        if (! $type instanceof ReflectionClass) {
            return $typeResolver->resolve($type);
        }

        foreach ($type->getAttributes() as $attribute) {
            $webonyxType = match ($attribute->getName()) {
                Attribute\ObjectType::class      => $this->buildObjectType($type),
                Attribute\InputObjectType::class => $this->buildInputObjectType($type),
                default                          => null,
            };

            if (null !== $webonyxType) {
                return $webonyxType;
            }
        }

        return $typeResolver->resolve($type);
    }

    private function buildObjectType(ReflectionClass $class): Webonyx\ObjectType
    {
        $attribute = $this->reader->firstClassMetadata($class, Attribute\ObjectType::class);

        $config = [
            'name'        => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
        ];

        $instance = null;
        if ($class->isSubclassOf(FieldsAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();

            $config['fields'] = new LazyWebonyxObjectFields($instance, $this->objectFieldResolver);
        }

        if ($class->isSubclassOf(InterfacesAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();

            $config['interfaces'] = new LazyWebonyxTypeIterator($instance->getInterfaces(...), $this->typeRegistry);
        }

        if ($class->isSubclassOf(IsTypeOfAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();

            $config['isTypeOf'] = $instance->isTypeOf(...);
        }

        if ($class->isSubclassOf(ResolveFieldAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();

            $config['resolveField'] = $instance->resolveField(...);
        }

        return new ObjectType($config, $this->objectFieldResolver);
    }

    private function buildInputObjectType(ReflectionClass $class): Webonyx\InputObjectType
    {
        $attribute = $this->reader->firstClassMetadata($class, Attribute\InputObjectType::class);

        $config = [
            'name'        => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
            'parseValue'  => $this->getTypeParseValue($class, $attribute),
        ];

        $instance = null;
        if ($class->isSubclassOf(FieldsAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();

            $config['fields'] = new LazyWebonyxInputObjectFields($instance, $this->inputObjectFieldResolver);
        }

        return new InputObjectType($config, $this->inputObjectFieldResolver);
    }

    private function getTypeName(
        ReflectionClass $class,
        Attribute\ObjectType|Attribute\InputObjectType|null $attribute,
    ): string {
        return $attribute?->name ?? $class->getName();
    }

    /**
     * @param ReflectionClass $class
     * @param Attribute\ObjectType|null $attribute
     *
     * @return string|null
     *
     * @todo Extract description from annotation when attribute is not set
     */
    private function getTypeDescription(
        ReflectionClass $class,
        Attribute\ObjectType|Attribute\InputObjectType|null $attribute,
    ): ?string {
        return $attribute?->description;
    }

    private function getTypeParseValue(ReflectionClass $class, ?Attribute\InputObjectType $attribute): ?callable
    {
        if ($attribute?->parseValue) {
            $factory = $this->container->get($attribute->parseValue);
        } elseif ($class->isSubclassOf(ParseValueAwareInterface::class)) {
            $factory = $class->newInstanceWithoutConstructor()->parseValue(...);
        }

        return $factory ?? null;
    }
}

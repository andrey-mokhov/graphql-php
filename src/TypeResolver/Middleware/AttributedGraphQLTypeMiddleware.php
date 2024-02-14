<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Attribute;
use Andi\GraphQL\Common\DefinitionAwareTrait;
use Andi\GraphQL\Common\InputObjectFactory;
use Andi\GraphQL\Common\LazyInputObjectFields;
use Andi\GraphQL\Common\LazyObjectFields;
use Andi\GraphQL\Common\LazyTypeIterator;
use Andi\GraphQL\Common\LazyTypeResolver;
use Andi\GraphQL\Common\ReflectionMethodWithAttribute;
use Andi\GraphQL\Common\ResolveType;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\WebonyxType\InputObjectType;
use Andi\GraphQL\WebonyxType\InterfaceType;
use Andi\GraphQL\WebonyxType\ObjectType;
use GraphQL\Type\Definition as Webonyx;
use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\ResolverInterface;

final class AttributedGraphQLTypeMiddleware implements MiddlewareInterface
{
    use DefinitionAwareTrait;

    public const PRIORITY = 1024;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly ObjectFieldResolverInterface $objectFieldResolver,
        private readonly InputObjectFieldResolverInterface $inputObjectFieldResolver,
        private readonly ResolverInterface $resolver,
    ) {
    }

    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        $class = \is_string($type) && \class_exists($type)
            ? new \ReflectionClass($type)
            : $type;

        if (! $class instanceof \ReflectionClass) {
            return $typeResolver->resolve($type);
        }

        $attributes = $class->getAttributes(Attribute\AbstractType::class, \ReflectionAttribute::IS_INSTANCEOF);
        foreach ($attributes as $attribute) {
            $webonyxType = match ($attribute->getName()) {
                Attribute\ObjectType::class => $this->buildObjectType($class),
                Attribute\InputObjectType::class => $this->buildInputObjectType($class),
                Attribute\InterfaceType::class => $this->buildInterfaceType($class),
                default => null,
            };

            if (null !== $webonyxType) {
                return $webonyxType;
            }
        }

        return $typeResolver->resolve($type);
    }

    private function buildObjectType(\ReflectionClass $class): Webonyx\ObjectType
    {
        $attribute = $this->reader->firstClassMetadata($class, Attribute\ObjectType::class);

        $config = [
            'name' => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
        ];

        $instance = null;
        if ($class->isSubclassOf(FieldsAwareInterface::class)) {
            $instance = $class->newInstanceWithoutConstructor();

            $config['fields'] = new LazyObjectFields($instance, $this->objectFieldResolver);
        }

        if ($class->isSubclassOf(InterfacesAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();
            /** @psalm-suppress UndefinedMethod */
            $config['interfaces'] = new LazyTypeIterator($instance->getInterfaces(...), $this->typeRegistry);
        }

        if ($class->isSubclassOf(IsTypeOfAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();
            /** @psalm-suppress UndefinedMethod */
            $config['isTypeOf'] = $instance->isTypeOf(...);
        }

        if ($class->isSubclassOf(ResolveFieldAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();
            /** @psalm-suppress UndefinedMethod */
            $config['resolveField'] = $instance->resolveField(...);
        }

        $type = new ObjectType($config, $this->objectFieldResolver);

        $this->registerAdditionalFieldByMethods($type, $class, Attribute\ObjectField::class);
        $this->registerAdditionalFieldByProperties($type, $class, Attribute\ObjectField::class);

        return $type;
    }

    private function buildInputObjectType(\ReflectionClass $class): Webonyx\InputObjectType
    {
        $attribute = $this->reader->firstClassMetadata($class, Attribute\InputObjectType::class);

        $config = [
            'name' => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
            'parseValue' => $this->getTypeParseValue($class, $attribute),
        ];

        if ($class->isSubclassOf(FieldsAwareInterface::class)) {
            $instance = $class->newInstanceWithoutConstructor();

            $config['fields'] = new LazyInputObjectFields($instance, $this->inputObjectFieldResolver);
        }

        $type = new InputObjectType($config, $this->inputObjectFieldResolver);

        $this->registerAdditionalFieldByMethods($type, $class, Attribute\InputObjectField::class);
        $this->registerAdditionalFieldByProperties($type, $class, Attribute\InputObjectField::class);

        return $type;
    }

    private function buildInterfaceType(\ReflectionClass $class): Webonyx\InterfaceType
    {
        $attribute = $this->reader->firstClassMetadata($class, Attribute\InterfaceType::class);

        $config = [
            'name' => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
            'resolveType' => $this->getResolveTypeFn($class, $attribute),
        ];

        $type = new InterfaceType($config, $this->objectFieldResolver);

        $this->registerAdditionalFieldByMethods($type, $class, Attribute\InterfaceField::class);

        return $type;
    }

    private function getResolveTypeFn(\ReflectionClass $class, ?Attribute\InterfaceType $attribute): callable
    {
        if ($attribute?->resolveType) {
            return new LazyTypeResolver($this->container->get($attribute->resolveType), $this->typeRegistry);
        }

        if (! $class->isInterface() && $class->isSubclassOf(ResolveTypeAwareInterface::class)) {
            return new LazyTypeResolver($class->getMethod('resolveType')->getClosure(), $this->typeRegistry);
        }

        return $this->container->get(ResolveType::class);
    }

    private function getTypeParseValue(\ReflectionClass $class, ?Attribute\InputObjectType $attribute): callable
    {
        if ($attribute?->factory) {
            return $this->container->get($attribute->factory);
        }

        if ($class->isSubclassOf(ParseValueAwareInterface::class)) {
            return $class->getMethod('parseValue')->getClosure();
        }

        return new InputObjectFactory($class, $this->resolver);
    }

    /**
     * @param DynamicObjectTypeInterface $type
     * @param \ReflectionClass $class
     * @param class-string $targetAttribute
     */
    private function registerAdditionalFieldByMethods(
        DynamicObjectTypeInterface $type,
        \ReflectionClass $class,
        string $targetAttribute,
    ): void {
        foreach ($class->getMethods() as $method) {
            if ($attribute = $this->reader->firstFunctionMetadata($method, $targetAttribute)) {
                $type->addAdditionalField(new ReflectionMethodWithAttribute($method, $attribute));
            }
        }
    }
    /**
     * @param DynamicObjectTypeInterface $type
     * @param \ReflectionClass $class
     * @param class-string $targetAttribute
     */
    private function registerAdditionalFieldByProperties(
        DynamicObjectTypeInterface $type,
        \ReflectionClass $class,
        string $targetAttribute,
    ): void {
        foreach ($class->getProperties() as $property) {
            if (null !== $this->reader->firstPropertyMetadata($property, $targetAttribute)) {
                $type->addAdditionalField($property);
            }
        }
    }
}

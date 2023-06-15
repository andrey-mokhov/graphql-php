<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Attribute;
use Andi\GraphQL\Common\InputObjectFactory;
use Andi\GraphQL\Common\LazyInputObjectFields;
use Andi\GraphQL\Common\LazyObjectFields;
use Andi\GraphQL\Common\LazyTypeIterator;
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
use Andi\GraphQL\WebonyxType\InterfaceType;
use Andi\GraphQL\WebonyxType\ObjectType;
use GraphQL\Type\Definition as Webonyx;
use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\ResolverInterface;

final class AttributedGraphQLTypeMiddleware implements MiddlewareInterface
{
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
        if (! $type instanceof ReflectionClass) {
            return $typeResolver->resolve($type);
        }

        $attributes = $type->getAttributes(Attribute\AbstractType::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($attributes as $attribute) {
            $webonyxType = match ($attribute->getName()) {
                Attribute\ObjectType::class      => $this->buildObjectType($type),
                Attribute\InputObjectType::class => $this->buildInputObjectType($type),
                Attribute\EnumType::class        => $this->buildEnumType($type),
                Attribute\InterfaceType::class   => $this->buildInterfaceType($type),
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

            $config['fields'] = new LazyObjectFields($instance, $this->objectFieldResolver);
        }

        if ($class->isSubclassOf(InterfacesAwareInterface::class)) {
            $instance ??= $class->newInstanceWithoutConstructor();

            $config['interfaces'] = new LazyTypeIterator($instance->getInterfaces(...), $this->typeRegistry);
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

        if ($class->isSubclassOf(FieldsAwareInterface::class)) {
            $instance = $class->newInstanceWithoutConstructor();

            $config['fields'] = new LazyInputObjectFields($instance, $this->inputObjectFieldResolver);
        }

        return new InputObjectType($config, $this->inputObjectFieldResolver);
    }

    /**
     * @param ReflectionEnum $class
     *
     * @return Webonyx\EnumType
     *
     * @todo Extract description & deprecationReason from annotation
     */
    private function buildEnumType(ReflectionEnum $class): Webonyx\EnumType
    {
        $attribute = $this->reader->firstClassMetadata($class, Attribute\EnumType::class);

        $config = [
            'name'        => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
            'values'      => [],
        ];

        foreach ($class->getCases() as $case) {
            $config['values'][$case->getName()] = [
                'value' => $case->getValue(),
            ];
        }

        return new Webonyx\EnumType($config);
    }

    private function buildInterfaceType(ReflectionClass $class): Webonyx\InterfaceType
    {
        $attribute = $this->reader->firstClassMetadata($class, Attribute\ObjectType::class);

        $config = [
            'name'        => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
        ];

        return new InterfaceType($config, $this->objectFieldResolver);
    }

    private function getTypeName(
        ReflectionClass $class,
        Attribute\AbstractType|null $attribute,
    ): string {
        return $attribute?->name ?? $class->getShortName();
    }

    /**
     * @param ReflectionClass $class
     * @param Attribute\AbstractType|null $attribute
     *
     * @return string|null
     *
     * @todo Extract description from annotation when attribute is not set
     */
    private function getTypeDescription(
        ReflectionClass $class,
        ?Attribute\AbstractType $attribute,
    ): ?string {
        return $attribute?->description;
    }

    private function getTypeParseValue(ReflectionClass $class, ?Attribute\InputObjectType $attribute): callable
    {
        if (null !== $attribute?->factory) {
            return $this->container->get($attribute->factory);
        } elseif ($class->isSubclassOf(ParseValueAwareInterface::class)) {
            return $class->getMethod('parseValue')->getClosure();
        } else {
            return new InputObjectFactory($class, $this->resolver);
        }
    }
}

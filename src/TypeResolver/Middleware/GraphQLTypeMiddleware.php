<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Common\LazyInputObjectFields;
use Andi\GraphQL\Common\LazyObjectFields;
use Andi\GraphQL\Common\LazyTypeIterator;
use Andi\GraphQL\Common\LazyTypeResolver;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;
use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;
use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Definition\Type\ScalarTypeInterface;
use Andi\GraphQL\Definition\Type\TypeInterface;
use Andi\GraphQL\Definition\Type\UnionTypeInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\GraphQL\WebonyxType\DynamicObjectType;
use GraphQL\Type\Definition as Webonyx;
use Psr\Container\ContainerInterface;

final class GraphQLTypeMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 2048;

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        if (! is_string($type) || ! is_subclass_of($type, TypeInterface::class)) {
            return $typeResolver->resolve($type);
        }

        if (is_subclass_of($type, ObjectTypeInterface::class)) {
            return $this->buildObjectType($this->container->get($type));
        }

        if (is_subclass_of($type, InputObjectTypeInterface::class)) {
            return $this->buildInputObjectType($this->container->get($type));
        }

        if (is_subclass_of($type, InterfaceTypeInterface::class)) {
            return $this->buildInterfaceType($this->container->get($type));
        }

        if (is_subclass_of($type, UnionTypeInterface::class)) {
            return $this->buildUnionType($this->container->get($type));
        }

        if (is_subclass_of($type, EnumTypeInterface::class)) {
            return $this->buildEnumType($this->container->get($type));
        }

        if (is_subclass_of($type, ScalarTypeInterface::class)) {
            return $this->buildScalarType($this->container->get($type));
        }

        return $typeResolver->resolve($type);
    }

    private function buildObjectType(ObjectTypeInterface $type): Webonyx\ObjectType
    {
        $objectFieldResolver = $this->container->get(ObjectFieldResolverInterface::class);

        $config = [
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'fields'      => new LazyObjectFields($type, $objectFieldResolver),
        ];

        if ($type instanceof InterfacesAwareInterface) {
            $config['interfaces'] = new LazyTypeIterator(
                $type->getInterfaces(...),
                $this->container->get(TypeRegistryInterface::class),
            );
        }

        if ($type instanceof IsTypeOfAwareInterface) {
            $config['isTypeOf'] = $type->isTypeOf(...);
        }

        if ($type instanceof ResolveFieldAwareInterface) {
            $config['resolveField'] = $type->resolveField(...);
        }

        if ($type instanceof DynamicObjectTypeInterface) {
            return new DynamicObjectType($type, $config);
        }

        return new Webonyx\ObjectType($config);
    }

    private function buildInputObjectType(InputObjectTypeInterface $type): Webonyx\InputObjectType
    {
        $fieldResolver = $this->container->get(InputObjectFieldResolverInterface::class);

        $config = [
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'fields'      => new LazyInputObjectFields($type, $fieldResolver),
        ];

        if ($type instanceof ParseValueAwareInterface) {
            $config['parseValue'] = $type->parseValue(...);
        }

        return new Webonyx\InputObjectType($config);
    }

    private function buildInterfaceType(InterfaceTypeInterface $type): Webonyx\InterfaceType
    {
        $objectFieldResolver = $this->container->get(ObjectFieldResolverInterface::class);

        $config = [
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'fields'      => new LazyObjectFields($type, $objectFieldResolver),
        ];

        if ($type instanceof ResolveTypeAwareInterface) {
            $typeRegistry = $this->container->get(TypeRegistryInterface::class);
            $config['resolveType'] = new LazyTypeResolver($type->resolveType(...), $typeRegistry);
        }

        return new Webonyx\InterfaceType($config);
    }

    private function buildUnionType(UnionTypeInterface $type): Webonyx\UnionType
    {
        $typeRegistry = $this->container->get(TypeRegistryInterface::class);

        $config = [
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'types'       => new LazyTypeIterator($type->getTypes(...), $typeRegistry),
        ];

        if ($type instanceof ResolveTypeAwareInterface) {
            $config['resolveType'] = new LazyTypeResolver($type->resolveType(...), $typeRegistry);
        }

        return new Webonyx\UnionType($config);
    }

    private function buildEnumType(EnumTypeInterface $type): Webonyx\EnumType
    {
        return new Webonyx\EnumType([
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'values'      => static function () use ($type): iterable {
                foreach ($type->getValues() as $value) {
                    yield $value->getName() => [
                        'name'              => $value->getName(),
                        'description'       => $value->getDescription(),
                        'value'             => $value->getValue(),
                        'deprecationReason' => $value->getDeprecationReason(),
                    ];
                }
            },
        ]);
    }

    private function buildScalarType(ScalarTypeInterface $type): Webonyx\CustomScalarType
    {
        return new Webonyx\CustomScalarType([
            'name'         => $type->getName(),
            'description'  => $type->getDescription(),
            'serialize'    => $type->serialize(...),
            'parseValue'   => $type->parseValue(...),
            'parseLiteral' => $type->parseLiteral(...),
        ]);
    }
}

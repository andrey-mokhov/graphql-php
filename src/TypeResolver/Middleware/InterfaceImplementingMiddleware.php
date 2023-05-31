<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Definition\Field\ArgumentInterface;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ComplexityAwareInterface;
use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use Andi\GraphQL\Definition\Field\ParseValueAwareInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\ResolveFieldAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;
use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;
use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Definition\Type\ScalarTypeInterface;
use Andi\GraphQL\Definition\Type\UnionTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Psr\Container\ContainerInterface;

final class InterfaceImplementingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function process(string $class, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        if (is_subclass_of($class, ObjectTypeInterface::class)) {
            return $this->buildObjectType($this->container->get($class));
        }

        if (is_subclass_of($class, InputObjectTypeInterface::class)) {
            return $this->buildInputObjectType($this->container->get($class));
        }

        if (is_subclass_of($class, InterfaceTypeInterface::class)) {
            return $this->buildInterfaceType($this->container->get($class));
        }

        if (is_subclass_of($class, UnionTypeInterface::class)) {
            return $this->buildUnionType($this->container->get($class));
        }

        if (is_subclass_of($class, EnumTypeInterface::class)) {
            return $this->buildEnumType($this->container->get($class));
        }

        if (is_subclass_of($class, ScalarTypeInterface::class)) {
            return $this->buildScalarType($this->container->get($class));
        }

        return $typeResolver->resolve($class);
    }

    private function buildObjectType(ObjectTypeInterface $type): Webonyx\ObjectType
    {
        $config = [
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'fields'      => $this->makeObjectFieldsFn($type),
        ];

        if ($type instanceof InterfacesAwareInterface) {
            $interfaces = $type->getInterfaces();
            $typeRegistry = $this->container->get(TypeRegistryInterface::class);

            $config['interfaces'] = static function () use ($interfaces, $typeRegistry): iterable {
                foreach ($interfaces as $interface) {
                    yield $typeRegistry->get($interface);
                }
            };
        }

        if ($type instanceof IsTypeOfAwareInterface) {
            $config['isTypeOf'] = $type->isTypeOf(...);
        }

        if ($type instanceof ResolveFieldAwareInterface) {
            $config['resolveField'] = $type->resolveField(...);
        }

        return new Webonyx\ObjectType($config);
    }

    private function buildInputObjectType(InputObjectTypeInterface $type): Webonyx\InputObjectType
    {
        $fields = $type->getFields();

        $config = [
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'fields'      => function () use ($fields): iterable {
                foreach ($fields as $field) {
                    $config = [
                        'name'              => $field->getName(),
                        'description'       => $field->getDescription(),
                        'deprecationReason' => $field->getDeprecationReason(),
                        'type'              => $this->makeFieldTypeFn($field),
                    ];

                    if ($field instanceof DefaultValueAwareInterface) {
                        $config['defaultValue'] = $field->getDefaultValue();
                    }

                    yield new Webonyx\InputObjectField($config);
                }
            },
        ];

        if ($type instanceof ParseValueAwareInterface) {
            $config['parseValue'] = $type->parseValue(...);
        }

        return new Webonyx\InputObjectType($config);
    }

    private function buildInterfaceType(InterfaceTypeInterface $type): Webonyx\InterfaceType
    {
        $config = [
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'fields'      => $this->makeObjectFieldsFn($type),
        ];

        if ($type instanceof ResolveTypeAwareInterface) {
            $config['resolveType'] = $type->resolveType(...);
        }

        return new Webonyx\InterfaceType($config);
    }

    private function makeObjectFieldsFn(ObjectTypeInterface | InterfaceTypeInterface $type): callable
    {
        return function () use ($type): iterable {
            foreach ($type->getFields() as $field) {
                $config = [
                    'name'              => $field->getName(),
                    'description'       => $field->getDescription(),
                    'deprecationReason' => $field->getDeprecationReason(),
                    'type'              => $this->makeFieldTypeFn($field),
                ];

                if ($field instanceof ArgumentsAwareInterface) {
                    $config['args'] = $this->extractArguments($field->getArguments());
                }

                if ($field instanceof ResolveAwareInterface) {
                    $config['resolve'] = $field->resolve(...);
                }

                if ($field instanceof ComplexityAwareInterface) {
                    $config['complexity'] = $field->complexity(...);
                }

                yield new Webonyx\FieldDefinition($config);
            }
        };
    }

    private function makeFieldTypeFn(TypeAwareInterface $field): callable
    {
        $typeRegistry = $this->container->get(TypeRegistryInterface::class);

        return static function () use ($field, $typeRegistry): Webonyx\Type {
            $type = $typeRegistry->get($field->getType());

            $typeMode = $field->getTypeMode();

            if (TypeAwareInterface::ITEM_IS_REQUIRED & $typeMode) {
                $type = Webonyx\Type::nonNull($type);
            }

            if (TypeAwareInterface::IS_LIST & $typeMode) {
                $type = Webonyx\Type::listOf($type);
            }

            if (TypeAwareInterface::IS_REQUIRED & $typeMode) {
                $type = Webonyx\Type::nonNull($type);
            }

            return $type;
        };
    }

    /**
     * @param iterable<ArgumentInterface> $arguments
     *
     * @return iterable
     */
    private function extractArguments(iterable $arguments): iterable
    {
        foreach ($arguments as $argument) {
            $config = [
                'name'        => $argument->getName(),
                'description' => $argument->getDescription(),
                'type'        => $this->makeFieldTypeFn($argument),
            ];

            if ($argument instanceof DefaultValueAwareInterface) {
                $config['defaultValue'] = $argument->getDefaultValue();
            }

            yield $config['name'] => $config;
        }
    }

    private function buildUnionType(UnionTypeInterface $type): Webonyx\UnionType
    {
        $typeRegistry = $this->container->get(TypeRegistryInterface::class);

        $config = [
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'types'       => static function () use ($type, $typeRegistry): iterable {
                foreach ($type->getTypes() as $name) {
                    yield $typeRegistry->get($name);
                }
            },
        ];

        if ($type instanceof ResolveTypeAwareInterface) {
            $config['resolveType'] = $type->resolveType(...);
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

<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Attribute;
use Andi\GraphQL\Common\LazyWebonyxObjectFields;
use Andi\GraphQL\Common\LazyWebonyxTypeIterator;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\GraphQL\WebonyxType\ObjectType;
use GraphQL\Type\Definition as Webonyx;
use Psr\Container\ContainerInterface;
use ReflectionClass;

final class AttributedGraphQLTypeMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly ObjectFieldResolverInterface $fieldResolver,
    ) {
    }

    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        if (! $type instanceof ReflectionClass) {
            return $typeResolver->resolve($type);
        }

        foreach ($type->getAttributes() as $attribute) {
            $webonyxType = match ($attribute->getName()) {
                Attribute\ObjectType::class => $this->buildObjectType($type),
                default                     => null,
            };

            if (null !== $webonyxType) {
                return $webonyxType;
            }
        }

        return $typeResolver->resolve($type);
    }

    private function buildObjectType(ReflectionClass $reflection): Webonyx\ObjectType
    {
        /** @var Attribute\ObjectType $attribute */
        $attribute = $reflection->getAttributes(Attribute\ObjectType::class)[0]->newInstance();

        $config = [
            'name'        => $attribute->name ?? $reflection->getShortName(),
            'description' => $attribute->description,
        ];

        $type = null;
        if ($reflection->isSubclassOf(FieldsAwareInterface::class)) {
            $type ??= $this->container->get($reflection->getName());

            $config['fields'] = new LazyWebonyxObjectFields($type, $this->fieldResolver);
        }

        if ($reflection->isSubclassOf(InterfacesAwareInterface::class)) {
            $type ??= $this->container->get($reflection->getName());

            $config['interfaces'] = new LazyWebonyxTypeIterator($type->getInterfaces(...), $this->typeRegistry);
        }

        if ($reflection->isSubclassOf(IsTypeOfAwareInterface::class)) {
            $type ??= $this->container->get($reflection->getName());

            $config['isTypeOf'] = $type->isTypeOf(...);
        }

        if ($reflection->isSubclassOf(ResolveFieldAwareInterface::class)) {
            $type ??= $this->container->get($reflection->getName());

            $config['resolveField'] = $type->resolveField(...);
        }

        return new ObjectType($this->fieldResolver, $config);
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use UnhandledMatchError;

class LazyTypeByReflectionType
{
    public function __construct(
        private readonly ReflectionType $type,
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly string $selfClassName,
    ) {
    }

    public function __invoke(): Webonyx\Type
    {
        if ($this->type instanceof ReflectionNamedType) {
            return $this->getTypeFromReflectionNamedType();
        }

        if ($this->type instanceof ReflectionUnionType) {
            return $this->getTypeFromReflectionUnionType();
        }

        throw new CantResolveGraphQLTypeException('Can\'t resolve GraphQL type from ReflectionType');
    }

    private function getTypeFromReflectionNamedType(): Webonyx\Type
    {
        if ($this->type->isBuiltin()) {
            try {
                $type = match ($this->type->getName()) {
                    'int' => $this->typeRegistry->get(Webonyx\IntType::class),
                    'string' => $this->typeRegistry->get(Webonyx\StringType::class),
                    'bool', 'true', 'false'   => $this->typeRegistry->get(Webonyx\BooleanType::class),
                    'float' => $this->typeRegistry->get(Webonyx\FloatType::class),
                };
            } catch (UnhandledMatchError $exception) {
                $message = 'Can\'t resolve GraphQL type from ReflectionType';
                throw new CantResolveGraphQLTypeException($message, 0, $exception);
            }
        } else {
            $name = $this->type->getName();

            $type = 'self' === $name || 'static' === $name
                ? $this->typeRegistry->get($this->selfClassName)
                : $this->typeRegistry->get($name);
        }

        return $this->type->allowsNull()
            ? $type
            : Webonyx\Type::nonNull($type);
    }

    /**
     * @return Webonyx\Type
     */
    private function getTypeFromReflectionUnionType(): Webonyx\Type
    {
        /** @var ReflectionType $type */
        $names = [];
        $types = [];
        $allowsNull = false;
        foreach ($this->type->getTypes() as $type) {
            if ($type->isBuiltin()) {
                $allowsNull = 'null' === $type->getName()
                    || throw new CantResolveGraphQLTypeException('UnionType must contains only ObjectTypes');

                continue;
            }

            $name = $type->getName();
            if ($this->typeRegistry->has($name)) {
                $gqlType = $this->typeRegistry->get($name);

                if (! $gqlType instanceof Webonyx\ObjectType) {
                    throw new CantResolveGraphQLTypeException('UnionType must contains only ObjectTypes');
                }

                $names[] = (string) $gqlType;
                $types[] = $name;
            } else {
                throw new CantResolveGraphQLTypeException(sprintf('Undefined ObjectType "%s" for UnionType', $name));
            }
        }

        sort($names);
        $name = implode('', $names) . 'UnionType';

        if ($this->typeRegistry->has($name)) {
            $existsType = $this->typeRegistry->get($name);

            return $this->type->allowsNull() || $allowsNull
                ? $existsType
                : Webonyx\Type::nonNull($existsType);
        }

        $unionType = new Webonyx\UnionType([
            'name'        => $name,
            'types'       => new LazyTypeIterator(fn() => $types, $this->typeRegistry),
            'resolveType' => new ResolveType($this->typeRegistry),
        ]);

        $this->typeRegistry->register($unionType);

        return $this->type->allowsNull() || $allowsNull
            ? $unionType
            : Webonyx\Type::nonNull($unionType);
    }
}

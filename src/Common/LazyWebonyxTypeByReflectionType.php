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

class LazyWebonyxTypeByReflectionType
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
                    'int'    => $this->typeRegistry->get(Webonyx\IntType::class),
                    'string' => $this->typeRegistry->get(Webonyx\StringType::class),
                    'bool'   => $this->typeRegistry->get(Webonyx\BooleanType::class),
                    'float'  => $this->typeRegistry->get(Webonyx\FloatType::class),
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
     *
     * @todo Declare method for ReflectionUnionType
     */
    private function getTypeFromReflectionUnionType(): Webonyx\Type
    {
    }
}

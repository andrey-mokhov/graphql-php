<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionParameter;

final class LazyWebonyxTypeByReflectionParameter extends LazyWebonyxTypeByReflectionType
{
    public function __construct(
        private readonly ReflectionParameter $parameter,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
        parent::__construct(
            $this->parameter->getType(),
            $this->typeRegistry,
            $this->parameter->getDeclaringClass()->getName(),
        );
    }

    public function __invoke(): Webonyx\Type
    {
        $type = parent::__invoke();

        return $this->parameter->isVariadic()
            ? Webonyx\Type::listOf($type)
            : $type;
    }
}

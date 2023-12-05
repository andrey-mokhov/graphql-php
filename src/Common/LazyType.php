<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;

final class LazyType
{
    public function __construct(
        private readonly TypeAwareInterface $type,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function __invoke(): Webonyx\Type
    {
        $type = $this->typeRegistry->get($this->type->getType());

        $mode = $this->type->getMode();

        if (TypeAwareInterface::ITEM_IS_REQUIRED === (TypeAwareInterface::ITEM_IS_REQUIRED & $mode)) {
            assert($type instanceof Webonyx\NullableType);
            $type = Webonyx\Type::nonNull($type);
        }

        if (TypeAwareInterface::IS_LIST === (TypeAwareInterface::IS_LIST & $mode)) {
            $type = Webonyx\Type::listOf($type);
        }

        if (TypeAwareInterface::IS_REQUIRED === (TypeAwareInterface::IS_REQUIRED & $mode)) {
            $type = Webonyx\Type::nonNull($type);
        }

        return $type;
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;

final class LazyWebonyxType
{
    public function __construct(
        private readonly TypeAwareInterface $type,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function __invoke(): Webonyx\Type
    {
        $type = $this->typeRegistry->get($this->type->getType());

        $typeMode = $this->type->getTypeMode();

        if (TypeAwareInterface::ITEM_IS_REQUIRED === (TypeAwareInterface::ITEM_IS_REQUIRED & $typeMode)) {
            $type = Webonyx\Type::nonNull($type);
        }

        if (TypeAwareInterface::IS_LIST === (TypeAwareInterface::IS_LIST & $typeMode)) {
            $type = Webonyx\Type::listOf($type);
        }

        if (TypeAwareInterface::IS_REQUIRED === (TypeAwareInterface::IS_REQUIRED & $typeMode)) {
            $type = Webonyx\Type::nonNull($type);
        }

        return $type;
    }
}

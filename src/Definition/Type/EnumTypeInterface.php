<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use Andi\GraphQL\Definition\Field\EnumValueInterface;

interface EnumTypeInterface extends TypeInterface
{
    /**
     * @return iterable<EnumValueInterface>
     */
    public function getValues(): iterable;
}

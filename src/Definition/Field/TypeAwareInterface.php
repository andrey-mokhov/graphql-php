<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

interface TypeAwareInterface
{
    final public const IS_REQUIRED      = 0b0001;
    final public const IS_LIST          = 0b0010;
    final public const ITEM_IS_REQUIRED = 0b0110;

    public function getType(): string;

    public function getTypeMode(): int;
}

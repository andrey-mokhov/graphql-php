<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

interface DefaultValueAwareInterface
{
    public function getDefaultValue(): mixed;
}

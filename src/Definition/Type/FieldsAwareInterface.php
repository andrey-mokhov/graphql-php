<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

interface FieldsAwareInterface
{
    public function getFields(): iterable;
}

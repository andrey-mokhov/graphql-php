<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

interface ParseValueAwareInterface
{
    /**
     * Converts incoming values from their array representation to something else (e.g. a value object).
     *
     * @param array<non-empty-string, mixed> $values
     *
     * @return mixed
     */
    public static function parseValue(array $values): mixed;
}

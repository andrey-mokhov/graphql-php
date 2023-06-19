<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use GraphQL\Language\AST\Node;

interface ScalarTypeInterface extends TypeInterface
{
    public function serialize(mixed $value): mixed;

    public function parseValue(mixed $value): mixed;

    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed;
}

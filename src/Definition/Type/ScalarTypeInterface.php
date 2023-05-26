<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use Andi\GraphQL\Definition\DefinitionInterface;
use GraphQL\Language\AST\Node;

interface ScalarTypeInterface extends DefinitionInterface
{
    public function serialize(mixed $value): mixed;

    public function parseValue(mixed $value): mixed;

    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed;
}

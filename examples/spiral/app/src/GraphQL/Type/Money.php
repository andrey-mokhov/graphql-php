<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ScalarTypeInterface;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;

final class Money implements ScalarTypeInterface
{
    public function getName(): string
    {
        return 'Money';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function serialize(mixed $value): int
    {
        return is_int($value)
            ? $value
            : throw new SerializationError("Int cannot represent non-integer value");
    }

    public function parseValue(mixed $value): int
    {
        return is_int($value)
            ? $value
            : throw new Error("Int cannot represent non-integer value");
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null): int
    {
        if ($valueNode instanceof IntValueNode) {
            return (int) $valueNode->value;
        }

        throw new Error("Int cannot represent non-integer value", $valueNode);
    }
}

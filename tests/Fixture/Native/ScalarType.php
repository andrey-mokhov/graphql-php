<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture\Native;

use Andi\GraphQL\Definition\Type\ScalarTypeInterface;
use GraphQL\Language\AST\Node;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class ScalarType implements ScalarTypeInterface
{
    public function getName(): string
    {
        return 'ScalarType';
    }

    public function getDescription(): ?string
    {
        return 'ScalarType description';
    }

    public function serialize(mixed $value): mixed
    {
    }

    public function parseValue(mixed $value): mixed
    {
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed
    {
    }
}

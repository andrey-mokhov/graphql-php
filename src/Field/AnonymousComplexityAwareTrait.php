<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

/**
 * @internal
 * @psalm-internal Andi\GraphQL
 */
trait AnonymousComplexityAwareTrait
{
    protected readonly mixed $complexity;

    public function complexity(int $childrenComplexity, array $args): int
    {
        return call_user_func($this->complexity, $childrenComplexity, $args);
    }
}

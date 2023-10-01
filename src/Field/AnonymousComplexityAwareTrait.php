<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

/**
 * @internal
 * @psalm-internal Andi\GraphQL
 */
trait AnonymousComplexityAwareTrait
{
    /**
     * @var callable
     */
    protected readonly mixed $complexityFn;

    public function complexity(int $childrenComplexity, array $args): int
    {
        return call_user_func($this->complexityFn, $childrenComplexity, $args);
    }
}

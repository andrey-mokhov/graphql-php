<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

interface ComplexityAwareInterface
{
    public function complexity(int $childrenComplexity, array $args): int;
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use Andi\GraphQL\Definition\DefinitionInterface;
use Andi\GraphQL\Definition\ResolveTypeAwareInterface;

interface UnionTypeInterface extends DefinitionInterface, ResolveTypeAwareInterface
{
    public function getTypes(): iterable;
}

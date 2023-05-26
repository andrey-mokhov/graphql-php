<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

use Andi\GraphQL\Definition\DefinitionInterface;
use Andi\GraphQL\Definition\TypeAwareInterface;

interface ArgumentInterface extends
    DefinitionInterface,
    TypeAwareInterface
{
}

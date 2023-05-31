<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

use Andi\GraphQL\Definition\DefinitionInterface;

interface ObjectFieldInterface extends DefinitionInterface, DeprecationReasonAwareInterface, TypeAwareInterface
{
}

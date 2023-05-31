<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use Andi\GraphQL\Definition\DefinitionInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;

/**
 * @method iterable<ObjectFieldInterface> getFields()
 */
interface ObjectTypeInterface extends DefinitionInterface, FieldsAwareInterface
{
}

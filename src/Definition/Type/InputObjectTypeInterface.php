<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

use Andi\GraphQL\Definition\DefinitionInterface;
use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;

/**
 * @method iterable<InputObjectFieldInterface> getFields()
 */
interface InputObjectTypeInterface extends DefinitionInterface, FieldsAwareInterface
{
}

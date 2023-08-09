<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture\Webonyx;

use GraphQL\Type\Definition as Webonyx;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class UnregisterObjectType extends Webonyx\ObjectType
{
    public string $name = 'UnregisterObjectType';
}

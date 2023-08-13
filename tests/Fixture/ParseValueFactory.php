<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class ParseValueFactory
{
    public function __invoke(array $data)
    {
        return 17;
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL;

use GraphQL\Type\Definition as Webonyx;

interface TypeRegistryInterface
{
    /**
     * @param non-empty-string $type
     */
    public function has(string $type): bool;

    /**
     * @param non-empty-string $type
     */
    public function get(string $type): Webonyx\Type;

    public function register(Webonyx\Type $type, string ...$aliases): void;
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL;

use GraphQL\Type\Definition as Webonyx;

interface TypeRegistryInterface
{
    public function has(string $type): bool;

    public function get(string $type): Webonyx\Type&Webonyx\NamedType;

    public function register(Webonyx\Type&Webonyx\NamedType $type, string ...$aliases): void;

    public function getTypes(): iterable;
}

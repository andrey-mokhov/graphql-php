<?php

declare(strict_types=1);

namespace Andi\GraphQL;

use GraphQL\Type\Definition as Webonyx;

interface TypeRegistryInterface
{
    public function __invoke(string $type): Webonyx\Type;

    public function has(string $type): bool;

    public function get(string $type): Webonyx\Type;

    public function register(Webonyx\Type $type, string ...$aliases): void;

    public function getTypes(): iterable;
}

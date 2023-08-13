<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\TypeRegistryInterface;

final class LazyTypeIterator
{
    /**
     * @var pure-callable(): iterable
     */
    private readonly mixed $types;

    public function __construct(
        callable $types,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
        $this->types = $types;
    }

    public function __invoke(): iterable
    {
        foreach (call_user_func($this->types) as $type) {
            yield $this->typeRegistry->get($type);
        }
    }
}

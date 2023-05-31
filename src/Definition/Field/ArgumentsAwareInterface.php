<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

interface ArgumentsAwareInterface
{
    /**
     * @return iterable<ArgumentInterface>
     */
    public function getArguments(): iterable;
}

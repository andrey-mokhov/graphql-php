<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

interface InterfacesAwareInterface
{
    /**
     * @return iterable<string>
     */
    public function getInterfaces(): iterable;
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Type;

interface InterfacesAwareInterface
{
    public function getInterfaces(): iterable;
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Exception;

use Exception;

class NotFoundException extends Exception implements GraphQLExceptionInterface
{
    public static function create(string $type): self
    {
        return new self(sprintf('Can\'t resolve GraphQL type: "%s"', $type));
    }
}

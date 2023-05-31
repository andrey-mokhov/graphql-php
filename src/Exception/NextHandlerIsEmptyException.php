<?php

declare(strict_types=1);

namespace Andi\GraphQL\Exception;

use DomainException;

class NextHandlerIsEmptyException extends DomainException implements GraphQLExceptionInterface
{
}

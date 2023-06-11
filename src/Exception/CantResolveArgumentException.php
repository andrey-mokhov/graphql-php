<?php

declare(strict_types=1);

namespace Andi\GraphQL\Exception;

use RuntimeException;

final class CantResolveArgumentException extends RuntimeException implements GraphQLExceptionInterface
{
}

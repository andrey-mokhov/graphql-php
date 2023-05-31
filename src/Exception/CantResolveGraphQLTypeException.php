<?php

declare(strict_types=1);

namespace Andi\GraphQL\Exception;

use Dotenv\Exception\ExceptionInterface;
use RuntimeException;

final class CantResolveGraphQLTypeException extends RuntimeException implements ExceptionInterface
{
}

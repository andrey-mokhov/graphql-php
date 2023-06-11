<?php

declare(strict_types=1);

namespace Andi\GraphQL\Exception;

use RuntimeException;

final class CantResolveGraphQLTypeException extends RuntimeException implements GraphQLExceptionInterface
{
}

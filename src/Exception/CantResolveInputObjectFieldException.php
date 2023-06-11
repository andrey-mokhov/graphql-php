<?php

declare(strict_types=1);

namespace Andi\GraphQL\Exception;

use RuntimeException;

final class CantResolveInputObjectFieldException extends RuntimeException implements GraphQLExceptionInterface
{
}

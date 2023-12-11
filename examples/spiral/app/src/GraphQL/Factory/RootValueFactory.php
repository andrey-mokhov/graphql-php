<?php

declare(strict_types=1);

namespace App\GraphQL\Factory;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;

final class RootValueFactory
{
    public function __invoke(string $operationType, OperationParams $params, DocumentNode $doc): array
    {
        return compact('operationType', 'params', 'doc');
    }
}

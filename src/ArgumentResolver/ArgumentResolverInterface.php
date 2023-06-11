<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver;

use GraphQL\Type\Definition as Webonyx;

interface ArgumentResolverInterface
{
    /**
     * @param mixed $argument
     *
     * @return array{name: string, type: Webonyx\Type, description: ?string, defaultValue: mixed}
     */
    public function resolve(mixed $argument): array;
}

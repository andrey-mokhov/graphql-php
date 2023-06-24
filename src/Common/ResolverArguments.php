<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use GraphQL\Type\Definition as Webonyx;

final class ResolverArguments
{
    public function __construct(
        public readonly mixed $object,
        public readonly array $args,
        public readonly mixed $context,
        public readonly Webonyx\ResolveInfo $info,
    ) {
    }
}

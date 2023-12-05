<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;

final class LazyTypeResolver
{
    /**
     * @var callable(mixed, mixed, Webonyx\ResolveInfo): ?string
     */
    private readonly mixed $type;

    /**
     * @param callable(mixed, mixed, Webonyx\ResolveInfo): ?string $type
     * @param TypeRegistryInterface $typeRegistry
     */
    public function __construct(
        callable $type,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
        $this->type = $type;
    }

    public function __invoke(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?Webonyx\ObjectType
    {
        return $this->typeRegistry->get(call_user_func($this->type, $value, $context, $info));
    }
}

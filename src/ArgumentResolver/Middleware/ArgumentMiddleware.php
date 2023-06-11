<?php

declare(strict_types=1);

namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Common\LazyWebonyxType;
use Andi\GraphQL\Definition\Field\ArgumentInterface;
use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use Andi\GraphQL\TypeRegistryInterface;

final class ArgumentMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 2048;

    public function __construct(
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function process(mixed $argument, ArgumentResolverInterface $argumentResolver): array
    {
        if (! $argument instanceof ArgumentInterface) {
            return $argumentResolver->resolve($argument);
        }

        $config = [
            'name'        => $argument->getName(),
            'description' => $argument->getDescription(),
            'type'        => new LazyWebonyxType($argument, $this->typeRegistry),
        ];

        if ($argument instanceof DefaultValueAwareInterface && $argument->hasDefaultValue()) {
            $config['defaultValue'] = $argument->getDefaultValue();
        }

        return $config;
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use Andi\GraphQL\Common\ResolverArguments;
use GraphQL\Type\Definition as Webonyx;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;

/**
 * @see Webonyx\FieldDefinition
 *
 * @phpstan-import-type FieldDefinitionConfig from Webonyx\FieldDefinition
 */
final class OuterObjectField extends Webonyx\FieldDefinition
{
    /**
     * @param FieldDefinitionConfig $config
     * @param class-string $class
     * @param string $method
     * @param array<string,string> $argumentsMap
     * @param ScopeInterface $scope
     * @param InvokerInterface $invoker
     */
    public function __construct(
        array $config,
        private readonly string $class,
        private readonly string $method,
        private readonly array $argumentsMap,
        private readonly ScopeInterface $scope,
        private readonly InvokerInterface $invoker,
    ) {
        $config['resolve'] = $this->resolve(...);

        parent::__construct($config);
    }

    /**
     * @param mixed $object
     * @param array<string,mixed> $args
     * @param mixed $context
     * @param Webonyx\ResolveInfo $info
     *
     * @return mixed
     */
    private function resolve(
        mixed $object,
        array $args,
        mixed $context,
        Webonyx\ResolveInfo $info,
    ): mixed {
        $parameters = [];
        foreach ($args as $name => $value) {
            $parameters[$this->argumentsMap[$name] ?? $name] = $value;
        }

        return $this->scope->runScope(
            [ResolverArguments::class => new ResolverArguments($object, $args, $context, $info)],
            fn () => $this->invoker->invoke([$this->class, $this->method], $parameters),
        );
    }
}

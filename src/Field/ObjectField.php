<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use Andi\GraphQL\Common\ResolverArguments;
use GraphQL\Type\Definition as Webonyx;
use Spiral\Core\InvokerInterface;

final class ObjectField extends Webonyx\FieldDefinition
{
    /**
     * @param array $config
     * @param string $method
     * @param array<string,string> $argumentsMap
     * @param InvokerInterface $invoker
     */
    public function __construct(
        array $config,
        private readonly string $method,
        private readonly array $argumentsMap,
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

        return $this->invoker->invoke(
            [$object, $this->method],
            ['resolverArguments' => new ResolverArguments($object, $args, $context, $info)] + $parameters,
        );
    }
}

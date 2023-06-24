<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Field\OuterObjectField;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\InvokerInterface;

abstract class AbstractOuterObjectFieldByReflectionMethodMiddleware extends AbstractFieldByReflectionMethodMiddleware
{
    public function __construct(
        ReaderInterface $reader,
        TypeRegistryInterface $typeRegistry,
        ArgumentResolverInterface $argumentResolver,
        private readonly InvokerInterface $invoker,
    ) {
        parent::__construct($reader, $typeRegistry, $argumentResolver);
    }

    protected function buildField(array $config, ReflectionMethod $method): Webonyx\FieldDefinition
    {
        $config['args'] = iterator_to_array($iterator = $this->getFieldArguments($method));

        return new OuterObjectField(
            $config,
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $iterator->getReturn(),
            $this->invoker,
        );
    }
}

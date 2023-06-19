<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Attribute\QueryField;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\InvokerInterface;

final class QueryFieldByReflectionMethodMiddleware extends AbstractFieldByReflectionMethodMiddleware
{
    public const PRIORITY = ObjectFieldByReflectionMethodMiddleware::PRIORITY + 256;

    protected string $targetAttribute = QueryField::class;

    public function __construct(
        ReaderInterface $reader,
        TypeRegistryInterface $typeRegistry,
        ArgumentResolverInterface $argumentResolver,
        private readonly InvokerInterface $invoker,
    ) {
        parent::__construct($reader, $typeRegistry, $argumentResolver);
    }

    protected function getFieldResolver(ReflectionMethod $method): callable
    {
        $invoker = $this->invoker;
        $class = $method->getDeclaringClass()->getName();
        $name = $method->getName();

        return static function (
            mixed $object,
            array $args,
            mixed $context,
            Webonyx\ResolveInfo $info
        ) use ($invoker, $class, $name): mixed {
            return $invoker->invoke([$class, $name], ['context' => $context, 'info' => $info] + $args);
        };
    }
}

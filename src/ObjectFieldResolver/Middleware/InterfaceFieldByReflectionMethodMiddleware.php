<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Attribute\InterfaceField;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\InvokerInterface;

final class InterfaceFieldByReflectionMethodMiddleware extends AbstractFieldByReflectionMethodMiddleware
{
    public const PRIORITY = ObjectFieldByReflectionMethodMiddleware::PRIORITY + 64;

    protected string $targetAttribute = InterfaceField::class;

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
        $name = $method->getName();

        return static function (
            $object,
            array $args,
            mixed $context,
            Webonyx\ResolveInfo $info
        ) use ($invoker, $name): mixed {
            return $invoker->invoke([$object, $name], ['context' => $context, 'info' => $info] + $args);
        };
    }
}

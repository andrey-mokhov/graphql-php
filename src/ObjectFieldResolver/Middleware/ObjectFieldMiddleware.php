<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ComplexityAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;

/**
 * @see Webonyx\FieldDefinition
 *
 * @phpstan-import-type FieldDefinitionConfig from Webonyx\FieldDefinition
 */
final class ObjectFieldMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 2048;

    public function __construct(
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly ArgumentResolverInterface $argumentResolver,
    ) {
    }

    public function process(mixed $field, ObjectFieldResolverInterface $fieldResolver): Webonyx\FieldDefinition
    {
        if (! $field instanceof ObjectFieldInterface) {
            return $fieldResolver->resolve($field);
        }

        /** @var FieldDefinitionConfig $config */
        $config = [
            'name' => $field->getName(),
            'description' => $field->getDescription(),
            'deprecationReason' => $field->getDeprecationReason(),
            'type' => new LazyType($field, $this->typeRegistry),
        ];

        if ($field instanceof ArgumentsAwareInterface) {
            $config['args'] = $this->extractArguments($field->getArguments());
        }

        if ($field instanceof ResolveAwareInterface) {
            /** @psalm-suppress UndefinedMethod */
            $config['resolve'] = $field->resolve(...);
        }

        if ($field instanceof ComplexityAwareInterface) {
            /** @psalm-suppress UndefinedMethod */
            $config['complexity'] = $field->complexity(...);
        }

        return new Webonyx\FieldDefinition($config);
    }

    private function extractArguments(iterable $arguments): iterable
    {
        foreach ($arguments as $argument) {
            yield $this->argumentResolver->resolve($argument);
        }
    }
}

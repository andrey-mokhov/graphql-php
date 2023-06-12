<?php

declare(strict_types=1);

namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Common\LazyWebonyxNodeType;
use Andi\GraphQL\Common\LazyWebonyxReflectionType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\InvokerInterface;

final class ReflectionMethodMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 4096;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly InvokerInterface $invoker,
    ) {
    }

    public function process(mixed $field, ObjectFieldResolverInterface $fieldResolver): Webonyx\FieldDefinition
    {
        if (! $field instanceof ReflectionMethod) {
            return $fieldResolver->resolve($field);
        }

        $attribute = $this->reader->firstFunctionMetadata($field, ObjectField::class);

        $config = [
            'name'              => $this->getFieldName($field, $attribute),
            'description'       => $this->getFieldDescription($field, $attribute),
            'type'              => $this->getFieldType($field, $attribute),
            'args'              => $this->getFieldArguments($field),
            'resolve'           => $this->getFieldResolver($field),
            'deprecationReason' => $this->getFieldDeprecationReason($field, $attribute),
        ];

        return new Webonyx\FieldDefinition($config);
    }

    private function getFieldName(ReflectionMethod $method, ?ObjectField $attribute): ?string
    {
        if (null !== $attribute?->name) {
            return $attribute->name;
        }

        $name = $method->getName();

        if (str_starts_with($name, 'get')) {
            $name = substr($name, 3);
        }

        return lcfirst($name);
    }

    /**
     * @param ReflectionMethod $method
     * @param ObjectField|null $attribute
     *
     * @return string|null
     *
     * @todo Extract description from annotation when attribute is not set.
     */
    private function getFieldDescription(ReflectionMethod $method, ?ObjectField $attribute): ?string
    {
        return $attribute?->description;
    }

    private function getFieldType(ReflectionMethod $method, ?ObjectField $attribute): callable
    {
        if (null !== $attribute?->type) {
            return new LazyWebonyxNodeType($attribute->type, $this->typeRegistry);
        }

        if (! $method->hasReturnType()) {
            throw new CantResolveGraphQLTypeException(sprintf(
                'Can\'t resolve GraphQL type for field "%s"',
                $method->getName()
            ));
        }

        return new LazyWebonyxReflectionType(
            $method->getReturnType(),
            $this->typeRegistry,
            $method->getDeclaringClass()->getName(),
        );
    }

    private function getFieldArguments( ReflectionMethod $method): iterable
    {
        foreach ($method->getParameters() as $parameter) {

        }
    }

    private function getFieldResolver(ReflectionMethod $method): callable
    {
        $invoker = $this->invoker;
        $name = $method->getName();
        return static function (
            $object,
            array $args,
            mixed $context,
            Webonyx\ResolveInfo $info
        ) use ($invoker, $name): mixed {
            return $invoker->invoke([$object, $name], ['args' => $args, 'context' => $context, 'info' => $info]);
        };
    }

    /**
     * @param ReflectionMethod $method
     * @param ObjectField|null $attribute
     *
     * @return string|null
     *
     * @todo Extract deprecation reason from annotation when attribute is not set
     */
    private function getFieldDeprecationReason(ReflectionMethod $method, ?ObjectField $attribute): ?string
    {
        return $attribute?->deprecationReason;
    }
}

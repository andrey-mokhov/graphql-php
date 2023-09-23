<?php

declare(strict_types=1);

namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use Andi\GraphQL\Definition\Field\DeprecationReasonAwareInterface;
use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;

final class InputObjectFieldMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 2048;

    public function __construct(
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function process(mixed $field, InputObjectFieldResolverInterface $fieldResolver): Webonyx\InputObjectField
    {
        if (! $field instanceof InputObjectFieldInterface) {
            return $fieldResolver->resolve($field);
        }

        $config = [
            'name'              => $field->getName(),
            'description'       => $field->getDescription(),
            'type'              => new LazyType($field, $this->typeRegistry),
        ];

        if ($field instanceof DeprecationReasonAwareInterface) {
            $config['deprecationReason'] = $field->getDeprecationReason();
        }

        if ($field instanceof DefaultValueAwareInterface && $field->hasDefaultValue()) {
            $config['defaultValue'] = $field->getDefaultValue();
        }

        return new Webonyx\InputObjectField($config);
    }
}

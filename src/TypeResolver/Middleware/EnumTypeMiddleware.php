<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Attribute;
use Andi\GraphQL\Common\DefinitionAwareTrait;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionEnum;
use Spiral\Attributes\ReaderInterface;

final class EnumTypeMiddleware implements MiddlewareInterface
{
    use DefinitionAwareTrait;

    public const PRIORITY = 4096;

    public function __construct(
        private readonly ReaderInterface $reader,
    ) {
    }

    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        if (! $type instanceof ReflectionEnum) {
            return $typeResolver->resolve($type);
        }

        return $this->buildEnumType($type, $this->reader->firstClassMetadata($type, Attribute\EnumType::class));
    }

    /**
     * @param ReflectionEnum $class
     * @param Attribute\EnumType|null $attribute
     *
     * @return Webonyx\EnumType
     *
     * @todo Extract description & deprecationReason from case annotation
     */
    private function buildEnumType(ReflectionEnum $class, ?Attribute\EnumType $attribute): Webonyx\EnumType
    {
        $config = [
            'name'        => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
            'values'      => [],
        ];

        foreach ($class->getCases() as $case) {
            $config['values'][$case->getName()] = [
                'value' => $case->getValue(),
                // 'description'       => todo
                // 'deprecationReason' => todo
            ];
        }

        return new Webonyx\EnumType($config);
    }
}

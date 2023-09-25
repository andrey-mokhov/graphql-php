<?php

declare(strict_types=1);

namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\Attribute;
use Andi\GraphQL\Common\DefinitionAwareTrait;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlockFactory;
use Spiral\Attributes\ReaderInterface;

final class EnumTypeMiddleware implements MiddlewareInterface
{
    use DefinitionAwareTrait;

    public const PRIORITY = AttributedGraphQLTypeMiddleware::PRIORITY + 256;

    public function __construct(
        private readonly ReaderInterface $reader,
    ) {
    }

    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        $enum = is_string($type) && enum_exists($type)
            ? new \ReflectionEnum($type)
            : $type;

        if ($type instanceof \ReflectionEnum) {
            return $this->buildEnumType($enum, $this->reader->firstClassMetadata($enum, Attribute\EnumType::class));
        }

        return $typeResolver->resolve($type);
    }

    private function buildEnumType(\ReflectionEnum $class, ?Attribute\EnumType $attribute): Webonyx\EnumType
    {
        $config = [
            'name'        => $this->getTypeName($class, $attribute),
            'description' => $this->getTypeDescription($class, $attribute),
            'values'      => [],
        ];

        foreach ($class->getCases() as $case) {
            $caseAttribute = $this->reader->firstConstantMetadata($case, Attribute\EnumCase::class);
            $config['values'][$this->getCaseName($case, $caseAttribute)] = [
                'value' => $case->getValue(),
                'description' => $this->getCaseDescription($case, $caseAttribute),
                'deprecationReason' => $this->getCaseDeprecationReason($case, $caseAttribute),
            ];
        }

        return new Webonyx\EnumType($config);
    }

    private function getCaseName(\ReflectionEnumUnitCase $case, ?Attribute\EnumCase $attribute): string
    {
        return null !== $attribute?->name
            ? $attribute->name
            : $case->getName();
    }

    private function getCaseDescription(\ReflectionEnumUnitCase $case, ?Attribute\EnumCase $attribute): ?string
    {
        if ($attribute?->description) {
            return $attribute->description;
        }

        if ($docComment = $case->getDocComment()) {
            return DocBlockFactory::createInstance()->create($docComment)->getSummary() ?: null;
        }

        return null;
    }

    private function getCaseDeprecationReason(\ReflectionEnumUnitCase $case, ?Attribute\EnumCase $attribute): ?string
    {
        if ($attribute?->deprecationReason) {
            return $attribute->deprecationReason;
        }

        if ($docComment = $case->getDocComment()) {
            $docBlock = DocBlockFactory::createInstance()->create($docComment);
            foreach ($docBlock->getTags() as $tag) {
                if ($tag instanceof Deprecated) {
                    return (string) $tag->getDescription() ?: null;
                }
            }
        }

        return null;
    }
}

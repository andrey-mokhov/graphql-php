<?php

declare(strict_types=1);

namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ComplexityAwareInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\MutationFieldInterface;
use Andi\GraphQL\Field\QueryFieldInterface;
use GraphQL\Type\Definition as Webonyx;

final class ConcatService implements
    QueryFieldInterface,
    MutationFieldInterface,
    ArgumentsAwareInterface,
    ResolveAwareInterface,
    ComplexityAwareInterface
{
    public function getName(): string
    {
        return 'concat';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getDeprecationReason(): ?string
    {
        return null;
    }

    public function getType(): string
    {
        return Webonyx\StringType::class;
    }

    public function getMode(): int
    {
        return TypeAwareInterface::IS_REQUIRED;
    }

    public function getArguments(): iterable
    {
        yield [
            'name' => 'parts',
            'type' => Webonyx\Type::nonNull(
                Webonyx\Type::listOf(
                    Webonyx\Type::nonNull(
                        Webonyx\Type::string()
                    )
                )
            ),
        ];

        yield new Argument(
            name: 'separator',
            type: 'String',
            mode: TypeAwareInterface::IS_REQUIRED,
            defaultValue: ' ',
        );
    }

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        return implode($args['separator'], $args['parts']);
    }

    public function complexity(int $childrenComplexity, array $args): int
    {
        return $childrenComplexity + 1;
    }
}

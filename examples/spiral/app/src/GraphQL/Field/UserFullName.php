<?php

declare(strict_types=1);

namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ComplexityAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use App\GraphQL\Type\User;
use GraphQL\Type\Definition as Webonyx;

final class UserFullName implements
    ObjectFieldInterface,
    ArgumentsAwareInterface,
    ResolveAwareInterface,
    ComplexityAwareInterface
{
    public function getName(): string
    {
        return 'fullName';
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
        return 'String';
    }

    public function getMode(): int
    {
        return TypeAwareInterface::IS_REQUIRED;
    }

    public function getArguments(): iterable
    {
        yield new Argument(
            name: 'separator',
            type: 'String',
            mode: TypeAwareInterface::IS_REQUIRED,
            defaultValue: ' ',
        );
    }

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        /** @var User $objectValue */
        return implode(
            $args['separator'],
            [
                $objectValue->getLastname(),
                $objectValue->getFirstname(),
                (new \ReflectionProperty($objectValue, 'middlename'))->getValue($objectValue),
            ],
        );
    }

    public function complexity(int $childrenComplexity, array $args): int
    {
        return $childrenComplexity + 1;
    }
}

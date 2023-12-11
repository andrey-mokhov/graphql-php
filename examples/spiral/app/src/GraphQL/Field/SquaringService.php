<?php

declare(strict_types=1);

namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\AbstractObjectField;
use Andi\GraphQL\Field\QueryFieldInterface;
use GraphQL\Type\Definition as Webonyx;

final class SquaringService extends AbstractObjectField implements QueryFieldInterface, ResolveAwareInterface
{
    protected string $name = 'square';
    protected string $type = 'Int';
    protected int $mode = TypeAwareInterface::IS_REQUIRED;

    public function getArguments(): iterable
    {
        yield new Argument(name: 'num', type: 'Int', mode: TypeAwareInterface::IS_REQUIRED);
    }

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        return $args['num'] * $args['num'];
    }
}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;
use GraphQL\Type\Definition as Webonyx;

/**
 * Low priority description.
 *
 * @internal
 * @psalm-internal Andi\Tests
 */
#[InputObjectType(name: 'HiPriorityInputObjectType', description: 'Hi priority description')]
class PriorityInputObjectType implements FieldsAwareInterface, ParseValueAwareInterface
{
    #[InputObjectField(name: 'bar', type: Webonyx\IDType::class)]
    private string $id;

    public function getFields(): iterable
    {
        yield new Webonyx\InputObjectField([
            'name' => 'foo',
            'type' => Webonyx\Type::int(),
        ]);
    }

    public static function parseValue(array $values): mixed
    {
        return 12;
    }
}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\Type\QueryType;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueryType::class)]
class QueryTypeTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $instance = new QueryType();

        self::assertInstanceOf(ObjectTypeInterface::class, $instance);
        self::assertInstanceOf(DynamicObjectTypeInterface::class, $instance);
    }

    public function testSignature(): void
    {
        $instance = new QueryType();

        self::assertSame('Query', $instance->getName());
        self::assertNull($instance->getDescription());
    }

    public function testGetFieldsViaAddAdditionalFields(): void
    {
        $fields = [
            new Webonyx\FieldDefinition(['name' => 'foo']),
            new Webonyx\FieldDefinition(['name' => 'bar']),
        ];

        $instance = new QueryType();
        foreach ($fields as $field) {
            $instance->addAdditionalField($field);
        }

        $result = $instance->getFields();

        if ($result instanceof \Traversable) {
            $result = \iterator_to_array($result, false);
        }

        self::assertSame($fields, $result);
    }
}

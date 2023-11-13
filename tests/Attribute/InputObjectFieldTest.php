<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Attribute;

use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputObjectField::class)]
#[UsesClass(AbstractDefinition::class)]
#[UsesClass(AbstractField::class)]
final class InputObjectFieldTest extends TestCase
{
    public function testDefinition(): void
    {
        $reflection = new \ReflectionClass(InputObjectField::class);

        $attributes = $reflection->getAttributes(\Attribute::class);
        self::assertCount(1, $attributes);

        /** @var \Attribute $attribute */
        $attribute = $attributes[0]->newInstance();

        self::assertSame(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY, $attribute->flags);
    }

    #[DataProvider('getData')]
    public function testProperties(array $expected, array $properties): void
    {
        $attribute = new InputObjectField(...$properties);

        self::assertSame($expected['name'] ?? null, $attribute->name);
        self::assertSame($expected['description'] ?? null, $attribute->description);
        self::assertSame($expected['type'] ?? null, $attribute->type);
        self::assertSame($expected['mode'] ?? null, $attribute->mode);
        self::assertSame($expected['deprecationReason'] ?? null, $attribute->deprecationReason);

        if (isset($expected['defaultValue']) || array_key_exists('defaultValue', $expected)) {
            self::assertTrue($attribute->hasDefaultValue());
            self::assertSame($expected['defaultValue'], $attribute->defaultValue);
        } else {
            self::assertFalse($attribute->hasDefaultValue());
        }
    }

    public static function getData(): iterable
    {
        yield 'empty attribute' => [
            'expected' => [],
            'properties' => [],
        ];

        $obj = new \stdClass();
        $obj->qwe = 'asd';
        yield 'full attribute' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'foo description',
                'type' => 'Int',
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'deprecationReason' => 'foo deprecation reason',
                'defaultValue' => $obj,
            ],
            'properties' => [
                'name' => 'foo',
                'description' => 'foo description',
                'type' => 'Int',
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'deprecationReason' => 'foo deprecation reason',
                'defaultValue' => $obj,
            ],
        ];

        yield 'full attribute with nullable defaultValue' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'foo description',
                'type' => 'Int',
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'deprecationReason' => 'foo deprecation reason',
                'defaultValue' => null,
            ],
            'properties' => [
                'name' => 'foo',
                'description' => 'foo description',
                'type' => 'Int',
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'deprecationReason' => 'foo deprecation reason',
                'defaultValue' => null,
            ],
        ];
    }
}

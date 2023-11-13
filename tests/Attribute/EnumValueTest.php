<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Attribute;

use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\EnumValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnumValue::class)]
#[UsesClass(AbstractDefinition::class)]
final class EnumValueTest extends TestCase
{
    public function testDefinition(): void
    {
        $reflection = new \ReflectionClass(EnumValue::class);

        $attributes = $reflection->getAttributes(\Attribute::class);
        self::assertCount(1, $attributes);

        /** @var \Attribute $attribute */
        $attribute = $attributes[0]->newInstance();

        self::assertSame(\Attribute::TARGET_CLASS_CONSTANT, $attribute->flags);
    }
    #[DataProvider('getData')]
    public function testProperties(array $expected, array $properties): void
    {
        $attribute = new EnumValue(...$properties);

        self::assertSame($expected['name'] ?? null, $attribute->name);
        self::assertSame($expected['description'] ?? null, $attribute->description);
        self::assertSame($expected['deprecationReason'] ?? null, $attribute->deprecationReason);
    }

    public static function getData(): iterable
    {
        yield 'empty attribute' => [
            'expected' => [],
            'properties' => [],
        ];

        yield 'full attribute' => [
            'expected' => [
                'name' => 'foo',
                'description' => 'foo description',
                'deprecationReason' => 'foo deprecation reason',
            ],
            'properties' => [
                'name' => 'foo',
                'description' => 'foo description',
                'deprecationReason' => 'foo deprecation reason',
            ],
        ];
    }

}

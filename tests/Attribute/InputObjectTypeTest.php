<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Attribute;

use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\InputObjectType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputObjectType::class)]
#[UsesClass(AbstractDefinition::class)]
class InputObjectTypeTest extends TestCase
{
    public function testDefinition(): void
    {
        $reflection = new \ReflectionClass(InputObjectType::class);

        $attributes = $reflection->getAttributes(\Attribute::class);
        self::assertCount(1, $attributes);

        /** @var \Attribute $attribute */
        $attribute = $attributes[0]->newInstance();

        self::assertSame(\Attribute::TARGET_CLASS, $attribute->flags);
    }
    #[DataProvider('getData')]
    public function testProperties(array $expected, array $properties): void
    {
        $attribute = new InputObjectType(...$properties);

        self::assertSame($expected['name'] ?? null, $attribute->name);
        self::assertSame($expected['description'] ?? null, $attribute->description);
        self::assertSame($expected['factory'] ?? null, $attribute->factory);
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
                'factory' => 'foo factory',
            ],
            'properties' => [
                'name' => 'foo',
                'description' => 'foo description',
                'factory' => 'foo factory',
            ],
        ];
    }
}

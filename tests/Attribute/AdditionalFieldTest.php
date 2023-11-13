<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Attribute;

use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdditionalField::class)]
#[CoversClass(AbstractField::class)]
#[CoversClass(AbstractDefinition::class)]
final class AdditionalFieldTest extends TestCase
{
    public function testDefinition(): void
    {
        $reflection = new \ReflectionClass(AdditionalField::class);

        $attributes = $reflection->getAttributes(\Attribute::class);
        self::assertCount(1, $attributes);

        /** @var \Attribute $attribute */
        $attribute = $attributes[0]->newInstance();

        self::assertSame(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE, $attribute->flags);
    }

    #[DataProvider('getData')]
    public function testProperties(array $expected, array $properties): void
    {
        $attribute = new AdditionalField(...$properties);

        self::assertSame($expected['targetType'], $attribute->targetType);
        self::assertSame($expected['name'] ?? null, $attribute->name);
        self::assertSame($expected['description'] ?? null, $attribute->description);
        self::assertSame($expected['type'] ?? null, $attribute->type);
        self::assertSame($expected['mode'] ?? null, $attribute->mode);
        self::assertSame($expected['deprecationReason'] ?? null, $attribute->deprecationReason);
    }

    public static function getData(): iterable
    {
        yield 'minimal attribute' => [
            'expected' => [
                'targetType' => 'foo',
            ],
            'properties' => [
                'targetType' => 'foo',
            ],
        ];

        yield 'full attribute' => [
            'expected' => [
                'targetType' => 'target',
                'name' => 'foo',
                'description' => 'foo description',
                'type' => 'Int',
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'deprecationReason' => 'foo deprecation reason',
            ],
            'properties' => [
                'targetType' => 'target',
                'name' => 'foo',
                'description' => 'foo description',
                'type' => 'Int',
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'deprecationReason' => 'foo deprecation reason',
            ],
        ];
    }
}

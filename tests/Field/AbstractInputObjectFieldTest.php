<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Field;

use Andi\GraphQL\Definition\Field\DefaultValueAwareInterface;
use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\AbstractInputObjectField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractInputObjectField::class)]
final class AbstractInputObjectFieldTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $instance = new class extends AbstractInputObjectField {
            public function __construct() {}
        };

        self::assertInstanceOf(InputObjectFieldInterface::class, $instance);
    }

    public function testHasNotDefaultValue(): void
    {
        $instance = new class(
            'foo',
            'Int',
            TypeAwareInterface::IS_REQUIRED,
            'foo description',
            'foo deprecation reason',
        ) extends AbstractInputObjectField {
        };

        self::assertSame('foo', $instance->getName());
        self::assertSame('Int', $instance->getType());
        self::assertSame(TypeAwareInterface::IS_REQUIRED, $instance->getMode());
        self::assertSame('foo description', $instance->getDescription());
        self::assertSame('foo deprecation reason', $instance->getDeprecationReason());
        self::assertFalse($instance->hasDefaultValue());
    }

    public function testHasDefaultValue(): void
    {
        $instance = new class(
            'foo',
            'Int',
            TypeAwareInterface::IS_REQUIRED,
            'foo description',
            'foo deprecation reason',
        ) extends AbstractInputObjectField implements DefaultValueAwareInterface {
            public function getDefaultValue(): mixed
            {
                return 'default value';
            }
        };

        self::assertSame('foo', $instance->getName());
        self::assertSame('Int', $instance->getType());
        self::assertSame(TypeAwareInterface::IS_REQUIRED, $instance->getMode());
        self::assertSame('foo description', $instance->getDescription());
        self::assertSame('foo deprecation reason', $instance->getDeprecationReason());
        self::assertTrue($instance->hasDefaultValue());
        self::assertSame('default value', $instance->getDefaultValue());
    }
}

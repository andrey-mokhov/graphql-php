<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Type;

use Andi\GraphQL\Definition\Type\TypeInterface;
use Andi\GraphQL\Type\AbstractType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractType::class)]
final class AbstractTypeTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $instance = \Mockery::mock(AbstractType::class);

        self::assertInstanceOf(TypeInterface::class, $instance);
    }

    public function testName(): void
    {
        $instance = new class extends AbstractType {
            protected string $name = 'foo';
        };

        self::assertSame('foo', $instance->getName());
        self::assertNull($instance->getDescription());
    }

    public function testDescription(): void
    {
        $instance = new class extends AbstractType {
            protected string $name = 'foo';
            protected string $description = 'foo description';
        };

        self::assertSame('foo', $instance->getName());
        self::assertSame('foo description', $instance->getDescription());
    }
}

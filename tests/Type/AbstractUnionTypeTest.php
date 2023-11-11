<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Type;

use Andi\GraphQL\Definition\Type\UnionTypeInterface;
use Andi\GraphQL\Type\AbstractUnionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractUnionType::class)]
final class AbstractUnionTypeTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $instance = \Mockery::mock(AbstractUnionType::class);

        self::assertInstanceOf(UnionTypeInterface::class, $instance);
    }

    public function testGetType(): void
    {
        $types = ['foo', 'bar'];

        $instance = new class ($types) extends AbstractUnionType {
            public function __construct(iterable $types)
            {
                $this->types = $types;
            }
        };

        self::assertSame($types, $instance->getTypes());
    }
}

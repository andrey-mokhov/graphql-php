<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Argument;

use Andi\GraphQL\Argument\AbstractArgument;
use Andi\GraphQL\Argument\Argument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Argument::class)]
#[CoversClass(AbstractArgument::class)]
final class ArgumentTest extends TestCase
{
    public function testConstructor(): void
    {
        $argument = new Argument('name', 'type', 123, 'description', 'defaultValue');

        self::assertSame('name', $argument->getName());
        self::assertSame('type', $argument->getType());
        self::assertSame(123, $argument->getTypeMode());
        self::assertSame('description', $argument->getDescription());
        self::assertSame('defaultValue', $argument->getDefaultValue());
        self::assertTrue($argument->hasDefaultValue());
    }
}

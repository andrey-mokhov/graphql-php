<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Attribute;
use Andi\GraphQL\Common\InputObjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

#[CoversClass(InputObjectFactory::class)]
#[UsesClass(Attribute\AbstractDefinition::class)]
#[UsesClass(Attribute\AbstractField::class)]
#[UsesClass(Attribute\InputObjectField::class)]
final class InputObjectFactoryTest extends TestCase
{
    public function testIsCallable(): void
    {
        $factory = new InputObjectFactory(new \ReflectionClass(new class {}), new Container());
        self::assertIsCallable($factory);
    }

    public function testInvoke(): void
    {
        $class = new class {
            #[Attribute\InputObjectField]
            public string $foo;

            public int $bar;

            #[Attribute\InputObjectField]
            public function setBar(int $bar): void
            {
                $this->bar = $bar;
            }
        };

        $factory = new InputObjectFactory(new \ReflectionClass($class), new Container());
        $result = $factory(['foo' => 'qwerty', 'bar' => 12]);

        self::assertSame('qwerty', $result->foo);
        self::assertSame(12, $result->bar);
    }

    public function testNameMapping(): void
    {
        $class = new class {
            #[Attribute\InputObjectField(name: 'defaultFoo')]
            public string $foo = 'foo value';

            #[Attribute\InputObjectField(name: 'defaultAsd')]
            public bool $asd = false;

            public int $bar;

            #[Attribute\InputObjectField(name: 'renamedBar')]
            public function setBar(int $bar, Container $knownParameter, string $otherParameter = null): void
            {
                $this->bar = $bar;
            }
        };

        $factory = new InputObjectFactory(new \ReflectionClass($class), new Container());
        $result = $factory(['defaultAsd' => true, 'renamedBar' => 15]);

        self::assertSame('foo value', $result->foo);
        self::assertTrue($result->asd);
        self::assertSame(15, $result->bar);
    }
}

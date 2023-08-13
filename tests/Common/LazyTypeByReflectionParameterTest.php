<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyTypeByReflectionParameter;
use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\TypeRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyTypeByReflectionParameter::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(LazyTypeByReflectionType::class)]
final class LazyTypeByReflectionParameterTest extends TestCase
{
    public function testIsCallable(): void
    {
        $class = new class {
            public function foo(int $foo): void {}
        };

        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getParameters() as $parameter) {
                break;
            }
        }

        $instance = new LazyTypeByReflectionParameter($parameter, new TypeRegistry());

        self::assertIsCallable($instance);
    }

    #[DataProvider('getDataForInvoke')]
    public function testInvoke(string $expected, object $class): void
    {
        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getParameters() as $parameter) {
                break;
            }
        }

        $instance = new LazyTypeByReflectionParameter($parameter, new TypeRegistry());

        $type = (string) call_user_func($instance);
        self::assertSame($expected, $type);
    }

    public static function getDataForInvoke(): iterable
    {
        yield 'int' => [
            'expected' => 'Int!',
            'class' => new class {
                public function foo(int $foo): void {}
            },
        ];

        yield 'nullable-int' => [
            'expected' => 'Int',
            'class' => new class {
                public function foo(int $foo = null): void {}
            },
        ];

        yield 'nullable-string' => [
            'expected' => 'String',
            'class' => new class {
                public function foo(?string $foo): void {}
            },
        ];

        yield 'nullable-bool' => [
            'expected' => 'Boolean',
            'class' => new class {
                public function foo(bool|null $foo): void {}
            },
        ];

        yield 'list-of-int' => [
            'expected' => '[Int!]',
            'class' => new class {
                public function foo(int ...$foo): void {}
            },
        ];
    }
}

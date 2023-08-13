<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyTypeIterator;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyTypeIterator::class)]
#[UsesClass(TypeRegistry::class)]
final class LazyTypeIteratorTest extends TestCase
{
    public function testIsCallable(): void
    {
        $instance = new LazyTypeIterator(static function () {}, new TypeRegistry());

        self::assertIsCallable($instance);
    }

    #[DataProvider('getDataForInvoke')]
    public function testInvoke(array $expected, callable $fn): void
    {
        $instance = new LazyTypeIterator($fn, new TypeRegistry());

        $types = [];
        foreach (call_user_func($instance) as $type) {
            $types[] = (string) $type;
        }

        self::assertSame($expected, $types);
    }

    public static function getDataForInvoke(): iterable
    {
        yield 'int' => [
            'expected' => ['Int'],
            'fn' => static fn(): iterable => [Webonyx\IntType::class],
        ];

        yield 'int-string' => [
            'expected' => ['Int', 'String'],
            'fn' => static fn(): iterable => [Webonyx\IntType::class, Webonyx\StringType::class],
        ];
    }
}

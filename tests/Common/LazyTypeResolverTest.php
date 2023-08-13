<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyTypeResolver;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyTypeResolver::class)]
#[UsesClass(TypeRegistry::class)]
final class LazyTypeResolverTest extends TestCase
{
    public function testIsCallable(): void
    {
        $instance = new LazyTypeResolver(static fn() => null, new TypeRegistry());

        self::assertIsCallable($instance);
    }

    #[DataProvider('getDataForInvoke')]
    public function testInvoke(
        string $expected,
        callable $fn,
        mixed $value,
        mixed $context,
        Webonyx\ResolveInfo $info,
    ): void {
        $typeRegistry = new TypeRegistry();
        $typeRegistry->register(new Webonyx\ObjectType(['name' => 'Foo']));
        $typeRegistry->register(new Webonyx\ObjectType(['name' => 'Bar']));

        $instance = new LazyTypeResolver($fn, $typeRegistry);
        $type = call_user_func($instance, $value, $context, $info);

        self::assertSame($expected, (string) $type);
    }

    public static function getDataForInvoke(): iterable
    {
        yield 'Foo' => [
            'expected' => 'Foo',
            'fn' => static fn(mixed $value, mixed $context, Webonyx\ResolveInfo $info) => 'Foo',
            'value' => null,
            'context' => null,
            'info' => \Mockery::mock(Webonyx\ResolveInfo::class),
        ];

        yield 'Bar' => [
            'expected' => 'Bar',
            'fn' => static fn(mixed $value, mixed $context, Webonyx\ResolveInfo $info) => $value,
            'value' => 'Bar',
            'context' => null,
            'info' => \Mockery::mock(Webonyx\ResolveInfo::class),
        ];
    }
}

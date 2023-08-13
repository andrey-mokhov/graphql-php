<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\ResolveType;
use Andi\GraphQL\TypeRegistry;
use Andi\Tests\GraphQL\Fixture\Webonyx\FooObjectType;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResolveType::class)]
#[UsesClass(TypeRegistry::class)]
final class ResolveTypeTest extends TestCase
{
    public function testIsCallable(): void
    {
        $instance = new ResolveType(new TypeRegistry());

        self::assertIsCallable($instance);
    }

    #[DataProvider('getDataForInvoke')]
    public function testInvoke(string $expected, mixed $object): void
    {
        $typeRegistry = new TypeRegistry();
        $typeRegistry->register(new FooObjectType([]), FooObjectType::class);

        $instance = new ResolveType($typeRegistry);
        $type = (string) call_user_func($instance, $object, null, \Mockery::mock(Webonyx\ResolveInfo::class));

        self::assertSame($expected, $type);
    }

    public static function getDataForInvoke(): iterable
    {
        yield 'null' => [
            'expected' => '',
            'object' => null,
        ];

        yield 'null-for-string' => [
            'expected' => '',
            'object' => 'non object',
        ];

        yield 'null-for-unknown-object' => [
            'expected' => '',
            'object' => new \stdClass(),
        ];

        yield 'null-for-non-ObjectType' => [
            'expected' => '',
            'object' => Webonyx\Type::int(),
        ];

        yield 'FooObjectType' => [
            'expected' => 'FooObjectType',
            'object' => new FooObjectType([]),
        ];

        yield 'children0FooObjectType' => [
            'expected' => 'FooObjectType',
            'object' => new class extends FooObjectType {
                public function __construct()
                {
                    parent::__construct([]);
                }
            },
        ];
    }
}

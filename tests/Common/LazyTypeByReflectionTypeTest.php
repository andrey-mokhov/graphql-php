<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyTypeByReflectionType;
use Andi\GraphQL\Common\LazyTypeIterator;
use Andi\GraphQL\Common\ResolveType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\Tests\GraphQL\Fixture\SimpleInterfaceType;
use Andi\Tests\GraphQL\Fixture\SimpleObjectType;
use Andi\Tests\GraphQL\Fixture\Webonyx\AsdObjectType;
use Andi\Tests\GraphQL\Fixture\Webonyx\BarObjectType;
use Andi\Tests\GraphQL\Fixture\Webonyx\FooObjectType;
use Andi\Tests\GraphQL\Fixture\Webonyx\NoObjectType;
use Andi\Tests\GraphQL\Fixture\Webonyx\UnregisterObjectType;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyTypeByReflectionType::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(LazyTypeIterator::class)]
#[UsesClass(ResolveType::class)]
final class LazyTypeByReflectionTypeTest extends TestCase
{
    private TypeRegistryInterface $typeRegistry;

    protected function setUp(): void
    {
        $this->typeRegistry = new TypeRegistry();
        $this->typeRegistry->register(new AsdObjectType([]), AsdObjectType::class);
        $this->typeRegistry->register(new BarObjectType([]), BarObjectType::class);
        $this->typeRegistry->register(new FooObjectType([]), FooObjectType::class);
        $this->typeRegistry->register(new NoObjectType([]), NoObjectType::class);
        $this->typeRegistry->register(new Webonyx\UnionType(['name' => 'AsdObjectTypeBarObjectTypeUnionType']));
    }

    public function testIsCallable(): void
    {
        $fn = static function (): int {return 1;};
        $reflection = (new \ReflectionFunction($fn))->getReturnType();

        $instance = new LazyTypeByReflectionType($reflection, $this->typeRegistry, 'foo');

        self::assertIsCallable($instance);
    }

    #[DataProvider('getDataForInvoke')]
    public function testInvoke(string $expected, callable $fn, string $exception = null): void
    {
        $reflection = (new \ReflectionFunction($fn))->getReturnType();

        $instance = new LazyTypeByReflectionType($reflection, $this->typeRegistry, 'FooObjectType');

        if (null !== $exception) {
            $this->expectException($exception);
        }

        $type = (string) call_user_func($instance);
        self::assertSame($expected, $type);
    }

    public static function getDataForInvoke(): iterable
    {
        yield 'int' => [
            'expected' => 'Int!',
            'fn' => static function (): int {return 1;},
        ];

        yield 'nullable-int' => [
            'expected' => 'Int',
            'fn' => static function (): int|null {return 1;},
        ];

        yield 'nullable-string' => [
            'expected' => 'String',
            'fn' => static function (): ?string {return null;},
        ];

        yield 'nullable-float' => [
            'expected' => 'Float',
            'fn' => static function (): null|float {return null;},
        ];

        if (PHP_VERSION_ID >= 80200) {
            $fn = null;
            eval('$fn = static function (): true {return true;};');

            yield 'bool' => [
                'expected' => 'Boolean!',
                'fn' => $fn,
            ];

            eval('$fn = static function (): false|null {return true;};');
            yield 'nullable-bool' => [
                'expected' => 'Boolean',
                'fn' => $fn,
            ];
        }

        yield 'raise-exception-when-unknown-builtin-type' => [
            'expected' => 'Foo',
            'fn' => static function (): void {},
            'exception' => CantResolveGraphQLTypeException::class,
        ];

        yield 'self' => [
            'expected' => 'FooObjectType!',
            'fn' => function (): self {return $this;},
        ];

        yield 'nullable-self' => [
            'expected' => 'FooObjectType',
            'fn' => function (): ?self {return $this;},
        ];

        yield 'static' => [
            'expected' => 'FooObjectType!',
            'fn' => function (): static {return $this;},
        ];

        yield 'webonyx-int' => [
            'expected' => 'Int!',
            'fn' => function (): Webonyx\IntType {return Webonyx\Type::int();},
        ];

        yield 'raise-exception-when-intersect-type' => [
            'expected' => 'Foo',
            'fn' => static function (): SimpleInterfaceType & SimpleObjectType {},
            'exception' => CantResolveGraphQLTypeException::class,
        ];

        yield 'union-type' => [
            'expected' => 'BarObjectTypeFooObjectTypeUnionType!',
            'fn' => static function (): BarObjectType|FooObjectType {return new FooObjectType([]);},
        ];

        yield 'exists-union-type' => [
            'expected' => 'AsdObjectTypeBarObjectTypeUnionType!',
            'fn' => static function (): AsdObjectType|BarObjectType {return new BarObjectType([]);},
        ];

        yield 'nullable-exists-union-type' => [
            'expected' => 'AsdObjectTypeBarObjectTypeUnionType',
            'fn' => static function (): AsdObjectType|BarObjectType|null {return null;},
        ];

        yield 'inverse-union-type' => [
            'expected' => 'BarObjectTypeFooObjectTypeUnionType!',
            'fn' => static function (): FooObjectType|BarObjectType {return new FooObjectType([]);},
        ];

        yield 'nullable-union-type' => [
            'expected' => 'BarObjectTypeFooObjectTypeUnionType',
            'fn' => static function (): BarObjectType|FooObjectType|null {return null;},
        ];

        yield 'raise-exception-when-not-object-type' => [
            'expected' => 'foo',
            'fn' => static function (): UnregisterObjectType|FooObjectType|null {return null;},
            'exception' => CantResolveGraphQLTypeException::class,
        ];

        yield 'raise-exception-when-non-object-type' => [
            'expected' => 'foo',
            'fn' => static function (): NoObjectType|FooObjectType|null {return null;},
            'exception' => CantResolveGraphQLTypeException::class,
        ];
    }
}

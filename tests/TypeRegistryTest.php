<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL;

use Andi\GraphQL\Common\LazyTypeIterator;
use Andi\GraphQL\Exception\NotFoundException;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\Tests\GraphQL\Fixture\Webonyx\BarObjectType;
use Andi\Tests\GraphQL\Fixture\Webonyx\FooObjectType;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeRegistry::class)]
#[UsesClass(NotFoundException::class)]
#[UsesClass(LazyTypeIterator::class)]
final class TypeRegistryTest extends TestCase
{
    private TypeRegistry $typeRegistry;

    protected function setUp(): void
    {
        $this->typeRegistry = new TypeRegistry();
        $this->typeRegistry->register(new FooObjectType([]), FooObjectType::class);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(TypeRegistryInterface::class, $this->typeRegistry);
    }

    #[DataProvider('getDataForHas')]
    public function testHas(bool $expected, string $name): void
    {
        self::assertSame($expected, $this->typeRegistry->has($name));
    }

    #[DataProvider('getDataForGet')]
    public function testGet(string $expected, string $name, string $exception = null): void
    {
        if (null !== $exception) {
            $this->expectException($exception);
        }

        $type = $this->typeRegistry->get($name);

        self::assertSame($expected, (string) $type);
    }

    public function testRegisterViaHas(): void
    {
        self::assertFalse($this->typeRegistry->has('BarObjectType'));
        self::assertFalse($this->typeRegistry->has(BarObjectType::class));
        self::assertFalse($this->typeRegistry->has('AnyName'));

        $this->typeRegistry->register(new BarObjectType([]), BarObjectType::class, 'AnyName');

        self::assertTrue($this->typeRegistry->has('BarObjectType'));
        self::assertTrue($this->typeRegistry->has(BarObjectType::class));
        self::assertTrue($this->typeRegistry->has('AnyName'));
    }

    public function testGetTypes(): void
    {
        $this->typeRegistry->register(new Webonyx\InterfaceType(['name' => 'BarInterface']));
        $fn = static function (): iterable {
            yield 'BarInterface';
        };

        $interfacesFn = new LazyTypeIterator($fn, $this->typeRegistry);

        $this->typeRegistry->register($barObjectType = new Webonyx\ObjectType([
            'name' => 'BarObjectType',
            'interfaces' => $interfacesFn,
        ]));

        $types = [];
        foreach ($this->typeRegistry->getTypes() as $type) {
            $types[] = $type;
        }

        self::assertSame([$barObjectType], $types);
    }

    public static function getDataForHas(): iterable
    {
        yield 'true-for-alias-int' => [
            'expected' => true,
            'name' => Webonyx\IntType::class,
        ];

        yield 'true-for-alias-string' => [
            'expected' => true,
            'name' => Webonyx\StringType::class,
        ];

        yield 'true-for-alias-boolean' => [
            'expected' => true,
            'name' => Webonyx\BooleanType::class,
        ];

        yield 'true-for-alias-float' => [
            'expected' => true,
            'name' => Webonyx\FloatType::class,
        ];

        yield 'true-for-alias-id' => [
            'expected' => true,
            'name' => Webonyx\IDType::class,
        ];

        yield 'true-for-int' => [
            'expected' => true,
            'name' => Webonyx\Type::INT,
        ];

        yield 'true-for-string' => [
            'expected' => true,
            'name' => Webonyx\Type::STRING,
        ];

        yield 'true-for-boolean' => [
            'expected' => true,
            'name' => Webonyx\Type::BOOLEAN,
        ];

        yield 'true-for-float' => [
            'expected' => true,
            'name' => Webonyx\Type::FLOAT,
        ];

        yield 'true-for-id' => [
            'expected' => true,
            'name' => Webonyx\Type::ID,
        ];

        yield 'false-for-unknown-type' => [
            'expected' => false,
            'name' => 'UnknownType',
        ];

        yield 'true-for-registered-type' => [
            'expected' => true,
            'name' => 'FooObjectType',
        ];

        yield 'true-for-registered-alias' => [
            'expected' => true,
            'name' => FooObjectType::class,
        ];
    }

    public static function getDataForGet(): iterable
    {
        yield 'string-as-alias' => [
            'expected' => 'String',
            'name' => Webonyx\StringType::class,
        ];

        yield 'int-as-alias' => [
            'expected' => 'Int',
            'name' => Webonyx\IntType::class,
        ];

        yield 'float-as-alias' => [
            'expected' => 'Float',
            'name' => Webonyx\FloatType::class,
        ];

        yield 'boolean-as-alias' => [
            'expected' => 'Boolean',
            'name' => Webonyx\BooleanType::class,
        ];

        yield 'id-as-alias' => [
            'expected' => 'ID',
            'name' => Webonyx\IDType::class,
        ];

        yield 'string-' => [
            'expected' => 'String',
            'name' => Webonyx\Type::STRING,
        ];

        yield 'int' => [
            'expected' => 'Int',
            'name' => Webonyx\Type::INT,
        ];

        yield 'float' => [
            'expected' => 'Float',
            'name' => Webonyx\Type::FLOAT,
        ];

        yield 'boolean' => [
            'expected' => 'Boolean',
            'name' => Webonyx\Type::BOOLEAN,
        ];

        yield 'id' => [
            'expected' => 'ID',
            'name' => Webonyx\Type::ID,
        ];

        yield 'FooObjectType-as-alias' => [
            'expected' => 'FooObjectType',
            'name' => FooObjectType::class,
        ];

        yield 'FooObjectType' => [
            'expected' => 'FooObjectType',
            'name' => 'FooObjectType',
        ];

        yield 'raise-exception-for-unknown-type' => [
            'expected' => '',
            'name' => 'UnknownType',
            'exception' => NotFoundException::class,
        ];
    }
}

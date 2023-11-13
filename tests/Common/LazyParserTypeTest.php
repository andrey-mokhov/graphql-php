<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyParserType;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Exception\NotFoundException;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyParserType::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(NotFoundException::class)]
final class LazyParserTypeTest extends TestCase
{
    private TypeRegistryInterface $typeRegistry;

    protected function setUp(): void
    {
        $this->typeRegistry = new TypeRegistry();
    }

    public function testIsCallable(): void
    {
        $instance = new LazyParserType('foo', 0, $this->typeRegistry);

        self::assertIsCallable($instance);
    }

    #[DataProvider('getDataForInvoke')]
    public function testInvoke(string $expected, string $type, int $mode = 0, string $exception = null): void
    {
        $instance = new LazyParserType($type, $mode, $this->typeRegistry);
        if (null !== $exception) {
            $this->expectException($exception);
        }
        $parsedType = call_user_func($instance);

        self::assertSame($expected, (string) $parsedType);
    }

    public static function getDataForInvoke(): iterable
    {
        yield 'class-name' => [
            'expected' => 'ID',
            'type' => Webonyx\IDType::class,
        ];

        yield 'required class-name' => [
            'expected' => 'ID!',
            'type' => Webonyx\IDType::class,
            'mode' => TypeAwareInterface::IS_REQUIRED,
        ];

        yield 'nullable-int' => [
            'expected' => 'Int',
            'type' => 'Int',
        ];

        yield 'required-string' => [
            'expected' => 'String!',
            'type' => 'String!',
        ];

        yield 'required string with mode' => [
            'expected' => 'String!',
            'type' => 'String',
            'mode' => TypeAwareInterface::IS_REQUIRED
        ];

        yield 'list-of-id' => [
            'expected' => '[ID]',
            'type' => '[ID]',
        ];

        yield 'list-of-id with mode' => [
            'expected' => '[ID]',
            'type' => Webonyx\IDType::class,
            'mode' => TypeAwareInterface::IS_LIST,
        ];

        yield 'no empty list-of-id with mode' => [
            'expected' => '[ID!]',
            'type' => Webonyx\IDType::class,
            'mode' => TypeAwareInterface::ITEM_IS_REQUIRED,
        ];

        yield 'no empty list of id with mode' => [
            'expected' => '[ID!]!',
            'type' => Webonyx\IDType::class,
            'mode' => TypeAwareInterface::ITEM_IS_REQUIRED | TypeAwareInterface::IS_REQUIRED,
        ];

        yield 'raise-exception' => [
            'expected' => 'ID',
            'type' => 'UnknownType',
            'mode' => 0,
            'exception' => NotFoundException::class,
        ];
    }
}

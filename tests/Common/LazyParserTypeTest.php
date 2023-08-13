<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyParserType;
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
        $instance = new LazyParserType('foo', $this->typeRegistry);

        self::assertIsCallable($instance);
    }

    #[DataProvider('getDataForInvoke')]
    public function testInvoke(Webonyx\Type $expected, string $type, string $exception = null): void
    {
        $instance = new LazyParserType($type, $this->typeRegistry);
        if (null !== $exception) {
            $this->expectException($exception);
        }
        $parsedType = call_user_func($instance);
        if ($parsedType instanceof Webonyx\WrappingType) {
            $parsedType = $parsedType->getWrappedType();
        }

        self::assertSame($expected, $parsedType);
    }

    public static function getDataForInvoke(): iterable
    {
        yield 'class-name' => [
            'expected' => Webonyx\Type::id(),
            'type' => Webonyx\IDType::class,
        ];

        yield 'nullable-int' => [
            'expected' => Webonyx\Type::int(),
            'type' => 'Int',
        ];

        yield 'required-string' => [
            'expected' => Webonyx\Type::string(),
            'type' => 'String!',
        ];

        yield 'list-of-id' => [
            'expected' => Webonyx\Type::id(),
            'type' => '[ID]',
        ];

        yield 'raise-exception' => [
            'expected' => Webonyx\Type::id(),
            'type' => 'UnknownType',
            'exception' => NotFoundException::class,
        ];
    }
}

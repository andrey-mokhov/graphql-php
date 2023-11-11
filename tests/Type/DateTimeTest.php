<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ScalarTypeInterface;
use Andi\GraphQL\Type\AbstractType;
use Andi\GraphQL\Type\DateTime;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTime::class)]
#[UsesClass(AbstractType::class)]
final class DateTimeTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $instance = new DateTime();

        self::assertInstanceOf(ScalarTypeInterface::class, $instance);
    }

    public function testName(): void
    {
        $instance = new DateTime();

        self::assertSame('DateTime', $instance->getName());
    }

    #[DataProvider('getDataForSerialize')]
    public function testSerialize(string $expected, mixed $value, string $exception = null): void
    {
        if (null !== $exception) {
            $this->expectException($exception);
        }

        $instance = new DateTime();

        $result = $instance->serialize($value);

        self::assertSame($expected, $result);
    }

    public static function getDataForSerialize(): iterable
    {
        yield 'simple date' => [
            'expected' => '2023-11-11T00:00:00+00:00',
            'value' => new \DateTimeImmutable('2023-11-11'),
        ];

        yield 'simple date 2' => [
            'expected' => '2024-02-29T01:02:03+04:00',
            'value' => new \DateTime('2024-02-29T01:02:03+04:00'),
        ];

        yield 'raise exception when value isn\'t DateTimeInterface' => [
            'expected' => '2024-02-29',
            'value' => '2024-02-29',
            'exception' => InvariantViolation::class,
        ];
    }

    #[DataProvider('getDataForParseValue')]
    public function testParseValue(?\DateTimeImmutable $expected, mixed $value, string $exception = null): void
    {
        if (null !== $exception) {
            $this->expectException($exception);
        }

        $instance = new DateTime();

        $result = $instance->parseValue($value);

        self::assertEquals($expected, $result);
    }

    public static function getDataForParseValue(): iterable
    {
        yield 'null for null' => [
            'expected' => null,
            'value' => null,
        ];

        yield 'DateTimeImmutable for DateTimeImmutable' => [
            'expected' => new \DateTimeImmutable('2024-02-29'),
            'value' => new \DateTimeImmutable('2024-02-29'),
        ];

        yield 'correct parse value without time' => [
            'expected' => new \DateTimeImmutable('2024-03-01'),
            'value' => '2024-03-01',
        ];

        yield 'correct parse value without timezone' => [
            'expected' => new \DateTimeImmutable('2024-03-01T01:02:03UTC'),
            'value' => '2024-03-01T01:02:03',
        ];

        yield 'correct parse value with timezone' => [
            'expected' => new \DateTimeImmutable('2024-03-01T12:13:59+07:00'),
            'value' => '2024-03-01T12:13:59+07:00',
        ];

        yield 'raise exception when value contains overload date' => [
            'expected' => new \DateTimeImmutable('2024-03-01'),
            'value' => '2024-02-30',
            'exception' => Error::class,
        ];

        yield 'raise exception when value contains overload time in hour' => [
            'expected' => new \DateTimeImmutable('2024-03-01T00:00:00UTC'),
            'value' => '2024-02-29T24:00:00UTC',
            'exception' => Error::class,
        ];

        yield 'raise exception when value contains overload time in minute' => [
            'expected' => new \DateTimeImmutable('2024-02-29T01:00:00UTC'),
            'value' => '2024-02-29T00:60:00UTC',
            'exception' => Error::class,
        ];
    }

    #[DataProvider('getDataForParseLiteral')]
    public function testParseLiteral(\DateTimeImmutable $expected, Node $valueNode, string $exception = null): void
    {
        if (null !== $exception) {
            $this->expectException($exception);
        }

        $instance = new DateTime();

        $result = $instance->parseLiteral($valueNode);

        self::assertEquals($expected, $result);
    }

    public static function getDataForParseLiteral(): iterable
    {
        yield 'correct parse value without time' => [
            'expected' => new \DateTimeImmutable('2024-02-29'),
            'valueNode' => new StringValueNode(['value' => '2024-02-29']),
        ];

        yield 'correct parse value without timezone' => [
            'expected' => new \DateTimeImmutable('2024-03-01T12:13:59+00:00'),
            'valueNode' => new StringValueNode(['value' => '2024-03-01T12:13:59']),
        ];

        yield 'correct parse value with time' => [
            'expected' => new \DateTimeImmutable('2024-03-01T12:13:59+07:00'),
            'valueNode' => new StringValueNode(['value' => '2024-03-01T12:13:59+07:00']),
        ];


        yield 'raise exception when value contains overload date' => [
            'expected' => new \DateTimeImmutable('2024-03-01'),
            'valueNode' => new StringValueNode(['value' => '2024-02-30']),
            'exception' => Error::class,
        ];

        yield 'raise exception when value contains overload time in hour' => [
            'expected' => new \DateTimeImmutable('2024-03-01T00:00:00UTC'),
            'valueNode' => new StringValueNode(['value' => '2024-02-29T24:00:00UTC']),
            'exception' => Error::class,
        ];

        yield 'raise exception when value contains overload time in minute' => [
            'expected' => new \DateTimeImmutable('2024-02-29T01:00:00UTC'),
            'valueNode' => new StringValueNode(['value' => '2024-02-29T00:60:00UTC']),
            'exception' => Error::class,
        ];

        yield 'raise exception when value isn\t StringValueNode' => [
            'expected' => new \DateTimeImmutable('2024-02-29'),
            'valueNode' => new IntValueNode(['value' => '2024-02-29']),
            'exception' => Error::class,
        ];
    }
}

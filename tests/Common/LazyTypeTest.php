<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyType;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\TypeRegistry;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyType::class)]
#[UsesClass(TypeRegistry::class)]
final class LazyTypeTest extends TestCase
{
    public function testIsCallable(): void
    {
        $class = new class implements TypeAwareInterface {
            public function getType(): string
            {
                return Webonyx\IntType::class;
            }

            public function getMode(): int
            {
                return 0;
            }
        };

        $instance = new LazyType($class, new TypeRegistry());

        self::assertIsCallable($instance);
    }

    #[DataProvider('getDataForInvoke')]
    public function testInvoke(string $expected, TypeAwareInterface $class): void
    {
        $instance = new LazyType($class, new TypeRegistry());
        $type = (string) call_user_func($instance);

        self::assertSame($expected, $type);
    }

    public static function getDataForInvoke(): iterable
    {
        yield 'nullable-id' => [
            'expected' => 'ID',
            'class' => new class implements TypeAwareInterface {
                public function getType(): string
                {
                    return Webonyx\IDType::class;
                }

                public function getMode(): int
                {
                    return 0;
                }
            },
        ];

        yield 'required-boolean' => [
            'expected' => 'Boolean!',
            'class' => new class implements TypeAwareInterface {
                public function getType(): string
                {
                    return 'Boolean';
                }

                public function getMode(): int
                {
                    return TypeAwareInterface::IS_REQUIRED;
                }
            },
        ];

        yield 'list-int' => [
            'expected' => '[Int]',
            'class' => new class implements TypeAwareInterface {
                public function getType(): string
                {
                    return 'Int';
                }

                public function getMode(): int
                {
                    return TypeAwareInterface::IS_LIST;
                }
            },
        ];

        yield 'required-item-list-float' => [
            'expected' => '[Float!]',
            'class' => new class implements TypeAwareInterface {
                public function getType(): string
                {
                    return 'Float';
                }

                public function getMode(): int
                {
                    return TypeAwareInterface::IS_LIST | TypeAwareInterface::ITEM_IS_REQUIRED;
                }
            },
        ];

        yield 'required-list-string' => [
            'expected' => '[String!]!',
            'class' => new class implements TypeAwareInterface {
                public function getType(): string
                {
                    return Webonyx\StringType::class;
                }

                public function getMode(): int
                {
                    return TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED;
                }
            },
        ];
    }
}

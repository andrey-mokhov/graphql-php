<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Attribute\AbstractDefinition;
use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Attribute\QueryField;
use Andi\GraphQL\Common\ReflectionMethodWithAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReflectionMethodWithAttribute::class)]
#[UsesClass(AbstractDefinition::class)]
#[UsesClass(AbstractField::class)]
final class ReflectionMethodWithAttributeTest extends TestCase
{
    public function testClass(): void
    {
        $object = new class {
            #[QueryField]
            public function foo(): void
            {
            }
        };

        $reflectionClass = new \ReflectionClass($object);
        $method = null;
        foreach ($reflectionClass->getMethods() as $method) {
            break;
        }

        $attribute = null;
        foreach ($method->getAttributes() as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            break;
        }

        $instance = new ReflectionMethodWithAttribute($method, $attribute);

        self::assertSame($method, $instance->method);
        self::assertSame($attribute, $instance->attribute);
    }
}

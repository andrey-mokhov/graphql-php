<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyObjectFields;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyObjectFields::class)]
final class LazyObjectFieldsTest extends TestCase
{
    public function testIsCallable(): void
    {
        $class = new class implements FieldsAwareInterface {
            public function getFields(): iterable
            {
                return [];
            }
        };

        $instance = new LazyObjectFields($class, \Mockery::mock(ObjectFieldResolverInterface::class));

        self::assertIsCallable($instance);
    }

    public function testInvoke(): void
    {
        $class = new class implements FieldsAwareInterface {
            public bool $isCalled = false;

            public function getFields(): iterable
            {
                $this->isCalled = true;

                return ['foo'];
            }
        };

        $resolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $resolver->shouldReceive('resolve')->andReturn(\Mockery::mock(Webonyx\FieldDefinition::class));

        $instance = new LazyObjectFields($class, $resolver);

        self::assertFalse($class->isCalled);

        $fields = \call_user_func($instance);
        foreach ($fields as $tmp) {
            // nothing
        }
        self::assertTrue($class->isCalled);
    }
}

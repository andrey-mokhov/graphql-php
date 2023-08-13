<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\LazyInputObjectFields;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyInputObjectFields::class)]
final class LazyInputObjectFieldsTest extends TestCase
{
    public function testIsCallable(): void
    {
        $instance = new LazyInputObjectFields(
            new class implements FieldsAwareInterface {
                public function getFields(): iterable
                {
                    return [];
                }
            },
            \Mockery::mock(InputObjectFieldResolverInterface::class),
        );

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

        $resolver = \Mockery::mock(InputObjectFieldResolverInterface::class);
        $resolver->shouldReceive('resolve')->andReturn(\Mockery::mock(Webonyx\InputObjectField::class));

        $instance = new LazyInputObjectFields($class, $resolver);

        self::assertFalse($class->isCalled);

        $fields = call_user_func($instance);
        foreach ($fields as $tmp) {
            // nothing
        }
        self::assertTrue($class->isCalled);
    }
}

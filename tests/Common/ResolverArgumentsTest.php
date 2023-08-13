<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Common;

use Andi\GraphQL\Common\ResolverArguments;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResolverArguments::class)]
final class ResolverArgumentsTest extends TestCase
{
    public function testClass(): void
    {
        $object = 'object';
        $args = ['foo' => 'bar', 'other' => 'example'];
        $context = new \stdClass();
        $info = \Mockery::mock(Webonyx\ResolveInfo::class);

        $instance = new ResolverArguments($object, $args, $context, $info);

        self::assertSame($object, $instance->object);
        self::assertSame($args, $instance->args);
        self::assertSame($context, $instance->context);
        self::assertSame($info, $instance->info);
    }
}

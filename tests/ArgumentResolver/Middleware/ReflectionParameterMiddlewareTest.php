<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\TypeRegistry;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\ReaderInterface;

#[CoversClass(ReflectionParameterMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
class ReflectionParameterMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $reader = \Mockery::mock(ReaderInterface::class);
        $middleware = new ReflectionParameterMiddleware($reader, new TypeRegistry());

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }
}

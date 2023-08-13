<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\WebonyxObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WebonyxObjectFieldMiddleware::class)]
final class WebonyxObjectFieldMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $middleware = new WebonyxObjectFieldMiddleware();

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $middleware = new WebonyxObjectFieldMiddleware();
        $middleware->process(null, $nextResolver);
    }

    public function testProcess(): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();

        $middleware = new WebonyxObjectFieldMiddleware();
        $middleware->process(new Webonyx\FieldDefinition(['name' => 'foo']), $nextResolver);
    }
}

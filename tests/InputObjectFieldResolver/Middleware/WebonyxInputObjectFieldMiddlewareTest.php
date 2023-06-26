<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\WebonyxInputObjectFieldMiddleware;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WebonyxInputObjectFieldMiddleware::class)]
class WebonyxInputObjectFieldMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $middleware = new WebonyxInputObjectFieldMiddleware();

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcessCallNextResolver(): void
    {
        $middleware = new WebonyxInputObjectFieldMiddleware();

        $nextResolver = \Mockery::mock(InputObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\InputObjectField(['name' => 'foo']));

        $middleware->process('any', $nextResolver);
    }

    public function testProcess(): void
    {
        $middleware = new WebonyxInputObjectFieldMiddleware();

        $nextResolver = \Mockery::mock(InputObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never()->andReturn(new Webonyx\InputObjectField(['name' => 'foo']));

        $inputObjectField = new Webonyx\InputObjectField(['name' => 'foo']);

        self::assertSame($inputObjectField, $middleware->process($inputObjectField, $nextResolver));
    }
}

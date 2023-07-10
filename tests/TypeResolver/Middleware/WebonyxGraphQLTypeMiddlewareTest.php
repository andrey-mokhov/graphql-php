<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\Middleware\WebonyxGraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

#[CoversClass(WebonyxGraphQLTypeMiddleware::class)]
final class WebonyxGraphQLTypeMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $middleware = new WebonyxGraphQLTypeMiddleware(new Container());

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(Webonyx\Type::id());

        $middleware = new WebonyxGraphQLTypeMiddleware(new Container());
        $middleware->process(null, $nextResolver);
    }

    public function testProcess(): void
    {
        $nextResolver = \Mockery::mock(TypeResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();
        $container = new Container();
        $container->bind(Webonyx\IntType::class, Webonyx\Type::int());

        $middleware = new WebonyxGraphQLTypeMiddleware($container);
        $middleware->process(Webonyx\IntType::class, $nextResolver);
    }

}

<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\ArgumentResolver\Middleware\MiddlewareInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArgumentConfigurationMiddleware::class)]
final class ArgumentConfigurationMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceOf(): void
    {
        $middleware = new ArgumentConfigurationMiddleware();

        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcess(): void
    {
        $config = [
            'name' => 'foo',
            'type' => Webonyx\Type::string(),
        ];

        $nextResolver = \Mockery::mock(ArgumentResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never()->andReturn([]);

        $middleware = new ArgumentConfigurationMiddleware();
        self::assertSame($config, $middleware->process($config, $nextResolver));
    }

    #[DataProvider('getCallNextResolver')]
    public function testProcessCallNextResolver(mixed $argument): void
    {
        $nextResolver = \Mockery::mock(ArgumentResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn([]);

        $middleware = new ArgumentConfigurationMiddleware();
        $middleware->process($argument, $nextResolver);
    }

    public static function getCallNextResolver(): iterable
    {
        yield 'without-name' => [
            'argument' => ['type' => Webonyx\Type::string()],
        ];

        yield 'without-type' => [
            'argument' => ['name' => 'foo'],
        ];

        yield 'other' => ['argument' => new \stdClass()];
    }
}

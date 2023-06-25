<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ArgumentResolver;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\ArgumentResolver\CantResolveArgumentResolver;
use Andi\GraphQL\Exception\CantResolveArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CantResolveArgumentResolver::class)]
class CantResolveArgumentResolverTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $resolver = new CantResolveArgumentResolver();

        self::assertInstanceOf(ArgumentResolverInterface::class, $resolver);
    }

    public function testResolve(): void
    {
        $resolver = new CantResolveArgumentResolver();

        $this->expectException(CantResolveArgumentException::class);
        $resolver->resolve(null);
    }
}

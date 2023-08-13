<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver;

use Andi\GraphQL\Exception\CantResolveObjectFieldException;
use Andi\GraphQL\ObjectFieldResolver\CantResolveObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CantResolveObjectFieldResolver::class)]
final class CantResolveObjectFieldResolverTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $resolver = new CantResolveObjectFieldResolver();

        self::assertInstanceOf(ObjectFieldResolverInterface::class, $resolver);
    }

    public function testResolve(): void
    {
        $resolver = new CantResolveObjectFieldResolver();

        $this->expectException(CantResolveObjectFieldException::class);
        $resolver->resolve(null);
    }
}

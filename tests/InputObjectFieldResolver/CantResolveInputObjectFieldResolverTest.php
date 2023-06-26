<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\InputObjectFieldResolver;

use Andi\GraphQL\Exception\CantResolveInputObjectFieldException;
use Andi\GraphQL\InputObjectFieldResolver\CantResolveInputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CantResolveInputObjectFieldResolver::class)]
class CantResolveInputObjectFieldResolverTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $resolver = new CantResolveInputObjectFieldResolver();

        self::assertInstanceOf(InputObjectFieldResolverInterface::class, $resolver);
    }

    public function testResolve(): void
    {
        $resolver = new CantResolveInputObjectFieldResolver();

        $this->expectException(CantResolveInputObjectFieldException::class);
        $resolver->resolve(null);
    }
}

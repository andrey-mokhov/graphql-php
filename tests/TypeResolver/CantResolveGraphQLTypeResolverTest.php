<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\TypeResolver;

use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeResolver\CantResolveGraphQLTypeResolver;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CantResolveGraphQLTypeResolver::class)]
final class CantResolveGraphQLTypeResolverTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $resolver = new CantResolveGraphQLTypeResolver();

        self::assertInstanceOf(TypeResolverInterface::class, $resolver);
    }

    public function testResolve(): void
    {
        $resolver = new CantResolveGraphQLTypeResolver();

        $this->expectException(CantResolveGraphQLTypeException::class);
        $resolver->resolve(null);
    }
}

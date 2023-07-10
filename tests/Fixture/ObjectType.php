<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

/**
 * @internal
 */
final class ObjectType implements ObjectTypeInterface
{
    public function getName(): string
    {
        return 'foo';
    }

    public function getDescription(): ?string
    {
        return 'foo description';
    }

    public function getFields(): iterable
    {
        yield new class implements ObjectFieldInterface {

            public function getName(): string
            {
                return 'field';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function getDeprecationReason(): ?string
            {
                return null;
            }

            public function getType(): string
            {
                return Webonyx\Type::ID;
            }

            public function getTypeMode(): int
            {
                return 0;
            }
        };
    }
}

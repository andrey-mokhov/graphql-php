<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture\Native;

use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ParseValueAwareInterface;
use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

/**
 * @internal
 * @psalm-internal Andi\Tests
 */
class InputObjectType implements InputObjectTypeInterface, ParseValueAwareInterface
{

    public function getName(): string
    {
        return 'InputObjectType';
    }

    public function getDescription(): ?string
    {
        return 'InputObjectType description';
    }

    public function getFields(): iterable
    {
        yield new class implements InputObjectFieldInterface {

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

            public function hasDefaultValue(): bool
            {
                return false;
            }

            public function getType(): string
            {
                return Webonyx\IDType::class;
            }

            public function getTypeMode(): int
            {
                return 0;
            }
        };
    }

    public static function parseValue(array $values): mixed
    {
        return 'parsed';
    }
}

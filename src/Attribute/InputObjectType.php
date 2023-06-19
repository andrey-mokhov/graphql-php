<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class InputObjectType extends AbstractType
{
    /**
     * @param string|null       $name
     * @param string|null       $description
     * @param class-string|null $factory     Class must have method: __invoke(array $values): mixed
     */
    public function __construct(
        ?string $name = null,
        ?string $description = null,
        public readonly ?string $factory = null,
    ) {
        parent::__construct($name, $description);
    }
}

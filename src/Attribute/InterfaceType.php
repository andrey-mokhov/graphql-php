<?php

declare(strict_types=1);

namespace Andi\GraphQL\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class InterfaceType extends AbstractType
{
    /**
     * @param string|null       $name
     * @param string|null       $description
     * @param class-string|null $typeResolver Class must have method:
     *                                        __invoke($value, $context, ResolveInfo $info): Webonyx\Type
     */
    public function __construct(
        ?string $name = null,
        ?string $description = null,
        public readonly ?string $typeResolver = null,
    ) {
        parent::__construct($name, $description);
    }

}

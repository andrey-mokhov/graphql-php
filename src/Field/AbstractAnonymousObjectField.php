<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

/**
 * @internal
 * @psalm-internal Andi\GraphQL
 */
abstract class AbstractAnonymousObjectField extends AbstractObjectField
{
    protected readonly mixed $resolveFn;
    protected readonly mixed $complexityFn;

    public function __construct(
        string $name,
        array $field,
        ?callable $resolve = null,
        ?callable $complexity = null,
    ) {
        $this->name = $name;
        $this->type = $field['type'];
        $this->mode = $field['mode'] ?? 0;

        if (isset($field['description']) && \is_string($field['description'])) {
            $this->description = $field['description'];
        }

        if (isset($field['deprecationReason']) && \is_string($field['deprecationReason'])) {
            $this->deprecationReason = $field['deprecationReason'];
        }

        if (isset($field['arguments']) && \is_iterable($field['arguments'])) {
            $this->arguments = $field['arguments'];
        }

        if (null !== $resolve) {
            $this->resolveFn = $resolve;
        }

        if (null !== $complexity) {
            $this->complexityFn = $complexity;
        }
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Definition\Field;

interface DeprecationReasonAwareInterface
{
    public function getDeprecationReason(): ?string;
}

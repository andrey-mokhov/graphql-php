<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use App\GraphQL\Field\UserFullName;

final class FullNameAwareInterface implements InterfaceTypeInterface
{
    public function getName(): string
    {
        return 'FullNameAwareInterface';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new UserFullName();
    }
}

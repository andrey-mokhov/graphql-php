<?php

declare(strict_types=1);

namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\Common\ResolverArguments;
use App\GraphQL\Type\User;
use App\GraphQL\Type\UserInterface;

final class UpperCaseName
{
    #[AdditionalField(targetType: User::class)]
    #[AdditionalField(targetType: UserInterface::class)]
    public function upperCaseName(ResolverArguments $arguments): string
    {
        /** @var User $user */
        $user = $arguments->object;

        return strtoupper($user->getDisplayName());
    }
}

<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
use Andi\GraphQL\Field\InputObjectField;
use GraphQL\Type\Definition as Webonyx;

final class LoginRequest implements InputObjectTypeInterface
{
    public function getName(): string
    {
        return 'LoginRequest';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new Webonyx\InputObjectField([
            'name' => 'login',
            'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
        ]);

        yield new InputObjectField(
            name: 'password',
            type: 'String',
            mode: TypeAwareInterface::IS_REQUIRED,
        );
    }
}

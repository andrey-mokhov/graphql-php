# Defining Mutation fields

By community agreement, Mutation fields provide for changes in system state.
The GraphQL `Mutation` type is an ObjectType, hence it can be defined in the same way,
like any other [ObjectType](object-type.md).

The library has a `Mutation` type, the capabilities of which are sufficient for defining fields.
By default, the class `Andi\GraphQL\Type\MutationType` is used for the `Mutation` type. Change class,
the implementing type `Mutation` can be found in [library settings](configure.md).

By default `Mutation` is disabled, it must be enabled first
in [library settings](configure.md).

Defining `Mutation` fields is possible using:
- php attribute `Andi\GraphQL\Attribute\MutationField`;
- by implementing the `Andi\GraphQL\Field\MutationFieldInterface` interface.

> :point_right: **Attention!!!** :point_left:
>
> The definition of `Mutation` and everything connected with them completely coincides with the definition of `Query`.
>
> The only difference is in the names of the attributes and interfaces used: instead of
> `#[QueryField]` will require attribute<br />`#[MutationField]`; instead of `QueryFieldInterface`
> implementation of the `MutationFieldInterface` interface will be required.
> Otherwise (field arguments or auxiliary interfaces) are completely identical.

## <a id="mutation-field-via-attribute">Defining Mutation fields using an attribute</a>

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Attribute\QueryField;

final class SimpleService
{
    #[MutationField(name: 'echo')]
    public function echoMessage(#[Argument] string $message): string
    {
        return 'echo: ' . $message;
    }
}
```

## Defining Mutation fields using the interface

Example implementation of the `MutationFieldInterface` interface:
```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\MutationFieldInterface;
use Andi\GraphQL\Field\QueryFieldInterface;
use GraphQL\Type\Definition as Webonyx;

final class ConcatService implements QueryFieldInterface, MutationFieldInterface
{
    public function getName(): string
    {
        return 'concat';
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
        return Webonyx\StringType::class;
    }

    public function getMode(): int
    {
        return TypeAwareInterface::IS_REQUIRED;
    }
}
```

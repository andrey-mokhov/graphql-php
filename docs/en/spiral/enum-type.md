# Defining EnumType

It is possible to define enum types:
- using the `Andi\GraphQL\Attribute\EnumType` attribute ([link](#enum-type-via-attribute));
- by implementing the `Andi\GraphQL\Definition\Type\EnumTypeInterface` interface ([link](#enum-type-via-interface)).

## <a id="enum-type-via-attribute">Defining using an attribute</a>

To define an enum type, use the `#[EnumType]` attribute, this attribute applies to
php transfers:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\EnumType;

#[EnumType]
enum DirectionEnum: string
{
    case asc = 'asc';

    case desc = 'desc';
}
```

The `#[EnumType]` attribute can contain the following constructor parameters:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            The name of the enum type. If not specified, the short name of the php enum is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            A description of the enumerated type as displayed in the GraphQL schema.
            If not specified, the php enum description specified in docBlock is used.
        </td>
    </tr>
</table>

> :point_right: **Note!**
>
> Valid values ​​for a GraphQL enum type are **all** (without exception) values
> corresponding php enum.

Valid enum values ​​can have the `Andi\GraphQL\Attribute\EnumValue` attribute set.

The `#[EnumValue]` attribute can contain the following constructor parameters:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            The name of the valid value. If not specified, the short name of the php value is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            A description of the valid value displayed in the GraphQL schema.
            If not specified, the php value description specified in docBlock is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            If the parameter is specified, then in the GraphQL schema this value will be marked as obsolete. As
            the reason will be indicated by the value of this parameter. If the parameter is not specified, but in docBlock
            php values ​​contain the <code>@deprecated</code> tag, then the comment of this tag will be used.
        </td>
    </tr>
</table>

## <a id="enum-type-via-interface">Definition by implementing an interface</a>

> :point_right: **Recommendation!**
>
> Use the abstract class [`Andi\GraphQL\Type\AbstractEnumType`](abstract-enum-type.md).
> It already implements the required methods.
>
> The library allows you to define GraphQL types in a way convenient for you.
> At the same time, the created structures can refer to each other.

Example implementation of the `EnumTypeInterface` interface:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\EnumValueInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;

final class AnimalEnum implements EnumTypeInterface
{
    public const DOG = 12;
    public const CAT = 15;

    public function getName(): string
    {
        return 'Animal';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getValues(): iterable
    {
        yield new class implements EnumValueInterface {
            ...
        };

        yield new class implements EnumValueInterface {
            ...
        };
    }
}
```

The `EnumTypeInterface` interface requires the following methods to be implemented:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the enum type.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return a description of the enum type that is mapped to the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getValues</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                The method must return an iterable structure (<code>array</code> or
                <code>\Traversable</code>) (an empty structure is not allowed) - list of valid values
                transfers.
            </p>
            <p>
                Each element of the structure must be an instance of a class that implements the interface
                <code>EnumValueInterface</code>
            </p>
        </td>
    </tr>
</table>

Valid values ​​of a GraphQL enum type must be implemented using an interface<br />
` Andi\GraphQL\Definition\Field\EnumValueInterface`.

> :point_right: **Рекомендация!**
>
> To define a valid enum type value, use the class
> [`Andi\GraphQL\Field\EnumValue`](abstract-enum-type.md#enum-value). It has already been implemented
> required methods.

Example implementation of the `EnumValueInterface` interface (see implementation of the `getValues` method):

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\EnumValueInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;
use Andi\GraphQL\Field\EnumValue;

final class AnimalEnum implements EnumTypeInterface
{
    public const DOG = 12;
    public const CAT = 15;

    ...

    public function getValues(): iterable
    {
        yield new class implements EnumValueInterface {
            public function getName(): string
            {
                return 'dog';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function getDeprecationReason(): ?string
            {
                return null;
            }

            public function getValue(): mixed
            {
                // Any php-value
                return AnimalEnum::DOG;
            }
        };

        yield new EnumValue(name: 'cat', value: AnimalEnum::CAT);
    }
}
```

The `EnumValueInterface` interface requires the following methods to be implemented:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the value to display in the GraphQL schema.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return the description value displayed in the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getDeprecationReason</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return a description of the reason for displaying in the GraphQL schema for which
            It is not recommended to use a value of an enumerated type and <code>null</code>,
            if there is no such reason.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            php value associated with the value of the GraphQL enumerated type.
            Can be any data type.
        </td>
    </tr>
</table>

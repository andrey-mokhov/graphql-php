# Defining ScalarType

Defining scalar types is possible by implementing an interface
`Andi\GraphQL\Definition\Type\ScalarTypeInterface`.

> :point_right: **Recommendation!**
>
> Use the abstract class [`Andi\GraphQL\Type\AbstractScalarType`](abstract-scalar-type.md).
> it already implements some of the required methods.
>
> The library allows you to define GraphQL types in a way convenient for you.
> At the same time, the created structures can refer to each other.

Example implementation of the `ScalarTypeInterface` interface:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ScalarTypeInterface;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;

final class Money implements ScalarTypeInterface
{
    public function getName(): string
    {
        return 'Money';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function serialize(mixed $value): int
    {
        return is_int($value)
            ? $value
            : throw new SerializationError("Int cannot represent non-integer value");
    }

    public function parseValue(mixed $value): int
    {
        return is_int($value)
            ? $value
            : throw new Error("Int cannot represent non-integer value");
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null): int
    {
        if ($valueNode instanceof IntValueNode) {
            return (int) $valueNode->value;
        }

        throw new Error("Int cannot represent non-integer value", $valueNode);
    }
}
```

The `ScalarTypeInterface` interface requires the implementation of the following methods:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the ScalarType.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return a ScalarType description that is mapped to a GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>serialize</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            The method must convert the resulting php structure <code>$value</code> into a scalar value.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>parseValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            The method must convert the GraphQL request variables into the internal PHP structure.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>parseLiteral</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            The method must convert data from the GraphQL request body into an internal PHP structure.
        </td>
    </tr>
</table>

[Definitions of scalar types](https://webonyx.github.io/graphql-php/type-definitions/scalars/)
identical to the definition in the `webonyx/graphql-php` core library.

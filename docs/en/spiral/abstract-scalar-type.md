# Abstract class AbstractScalarType

Abstract class `Andi\GraphQL\Type\AbstractScalarType` allows you to define scalar GraphQL types
without the need to implement the `getName` and `getDescription` methods. These methods are already implemented in the abstract
class, you just need to set the values of the corresponding properties to determine the result of these methods.

The `Money` type definition ([example from related document](scalar-type.md)) might look like this
way:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Type\AbstractScalarType;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;

final class Money implements AbstractScalarType
{
    protected string $name = 'Money';

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

The abstract class `AbstractScalarType` partially implements the methods required in the interface
`ScalarTypeInterface`. You will need to implement the following methods:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>serialize</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            The method must convert the resulting PHP structure <code>$value</code> into a scalar value.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>parseValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            The method must convert the GraphQL request variables into an internal php-structure.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>parseLiteral</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            The method must convert data from the GraphQL request body into an internal php-structure.
        </td>
    </tr>
</table>

[Defining Scalar Types](https://webonyx.github.io/graphql-php/type-definitions/scalars/)
identical to the definition in the reference library `webonyx/graphql-php`.

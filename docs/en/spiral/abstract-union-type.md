# Abstract class AbstractUnionType

The abstract class `Andi\GraphQL\Type\AbstractUnionType` allows you to define GraphQL union types
without the need to implement methods. The main methods are already implemented in the abstract class, all you need is
set the values ​​of its properties to determine the result of the implemented methods.

An example of an abstract class implementation:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Type\AbstractUnionType;
use GraphQL\Type\Definition as Webonyx;

final class ExampleAbstractUnionType extends AbstractUnionType implements ResolveTypeAwareInterface
{
    protected string $name = 'ExampleAbstractUnionType';

    protected iterable $types = [
        User::class,
        'pet',
    ];

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        if ($value instanceof User) {
            return 'User';
        }

        if (is_string($value)) {
            return Pet::class;
        }

        return null;
    }
}
```

When implementing a GraphQL union type using the abstract class `AbstractUnionType` you must
determine the values ​​of the following properties:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>$name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">The name of the union type, <b>required</b> must be defined.</td>
    </tr>
    <tr>
        <td valign="top"><code>$description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            A description of the union type that is displayed in the GraphQL schema.
            Don't define a value unless a description is needed.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$types</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Iterable structure (<code>array</code> or <code>\Traversable</code>)
                (an empty structure is not allowed) containing a list of object type names,
                components of the union type.
            </p>
            <p>
                Valid elements of an iterable structure are short names
                <a href="object-type.md">GraphQL ObjectTypes</a> or php class names,
                implementing the corresponding GraphQL ObjectType.
            </p>
        </td>
    </tr>
</table>

A class defining a UnionType GraphQL can implement the `ResolveTypeAwareInterface` interface
(see example above).

The `ResolveTypeAwareInterface` interface requires the following method to be implemented:
<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>resolveType</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            The method should parse the structure of the first parameter <code>$value</code> and return
            the name of the GraphQL ObjectType associated with this structure. Short name is acceptable
            GraphQL ObjectType or the name of a PHP class that implements the corresponding ObjectType.
        </td>
    </tr>
</table>

# Defining UnionType

Defining UnionType is possible by implementing an interface
`Andi\GraphQL\Definition\Type\UnionTypeInterface`.

> :point_right: **Recommendation!**
>
> Use the abstract class [`Andi\GraphQL\Type\AbstractUnionType`](abstract-union-type.md).
> It already implements the required methods.
>
> The library allows you to define GraphQL types in a way convenient for you.
> At the same time, the created structures can refer to each other.

Example implementation of the `UnionTypeInterface` interface:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Definition\Type\UnionTypeInterface;
use GraphQL\Type\Definition as Webonyx;

final class UserPetUnion implements UnionTypeInterface, ResolveTypeAwareInterface
{
    public function getName(): string
    {
        return 'UserPetUnion';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getTypes(): iterable
    {
        yield 'User';
        yield Pet::class;
    }

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        if ($value instanceof User) {
            return User::class;
        }

        if (is_string($value)) {
            return 'pet';
        }

        return null;
    }
}
```

The `UnionTypeInterface` interface requires the following methods to be implemented:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the UnionType.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return a description of the UnionType that is rendered in the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getTypes</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                The method must return an iterable structure (<code>array</code> or
                <code>\Traversable</code>) (an empty structure is not allowed) - a list of ObjectType names,
                components of the UnionType.
            </p>
            <p>
                Valid values can be short names
                <a href="object-type.md">GraphQL ObjectTypes</a> or php class names,
                implementing the corresponding GraphQL ObjectType.
            </p>
        </td>
    </tr>
</table>

A class defining a generic GraphQL type can implement the `ResolveTypeAwareInterface` interface
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
            GraphQL ObjectType or the name of a php class that implements the corresponding ObjectType.
        </td>
    </tr>
</table>

> :point_right: **Note!**
>
> When using attributes to define fields (Query, Mutation, ObjectType, InterfaceType),
> where the data type is not explicitly specified, the library tries to independently determine the field type.
>
> If a disjunction of classes is specified (each of which defines a GraphQL ObjectType),
> the library will collect short class names, sort them, concatenate the names, adding
> postfix UnionType, and the resulting name will try to be found in the GraphQL type registry. If unifying
> GraphQL type with this name will not be found, the library will register it.
>
> ```php
> #[ObjectField]
> public function getInitiator(): User|Admin|System
> {
>     ...
> }
> ```
>
> In the example above, a GraphQL UnionType will be created and registered with the name: `AdminSystemUserUnionType`.

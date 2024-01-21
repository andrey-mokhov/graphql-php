# GraphQL TypeRegistry

The library uses a single registry of GraphQL types: `Andi\GraphQL\TypeRegistry`.

The registry implements the `Andi\GraphQL\TypeRegistryInterface` interface:

```php
namespace Andi\GraphQL;

use GraphQL\Type\Definition as Webonyx;

interface TypeRegistryInterface
{
    public function has(string $type): bool;

    public function get(string $type): Webonyx\Type&Webonyx\NamedType;

    public function register(Webonyx\Type&Webonyx\NamedType $type, string ...$aliases): void;

    public function getTypes(): iterable;
}
```

Purpose of interface methods:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Method description</th>
    </tr>
    <tr>
        <td valign="top"><code>has</code></td>
        <td valign="top"><code>bool</code></td>
        <td valign="top">
            <p>
                Returns <code>true</code> if the requested GraphQL type name is registered in the registry,
                and <code>false</code> otherwise.
            </p>
            <p>
                GraphQL type aliases are also taken into account when searching the registry.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>get</code></td>
        <td valign="top"><code>Webonyx\Type & Webonyx\NamedType</code></td>
        <td valign="top">
            For the requested name, returns a GraphQL type definition. If the GraphQL type name is missing
            in the registry - an exception will be thrown.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>register</code></td>
        <td valign="top"><code>void</code></td>
        <td valign="top">
            <p>
                Registering a GraphQL type in the registry. The second and subsequent parameters can be listed
                aliases of the registered type, for example: the name of the class that defines the GraphQL type.
            </p>
            <p>
                As an example: when registering a scalar type <code>DateTime</code> it makes sense
                list the following aliases: <code>\DateTimeInterface::class</code>,
                <code>\DateTimeImmutable::class</code>.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getTypes</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            Service method. Returns object types registered in the registry that implement some
            interface. Required to map object types to the GraphQL schema (see configuration option
            <code>types</code> <a href="https://webonyx.github.io/graphql-php/schema-definition/#configuration-options">Configuration Options</a>).
        </td>
    </tr>
</table>

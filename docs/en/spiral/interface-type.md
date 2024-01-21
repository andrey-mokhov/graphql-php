# Defining InterfaceType

Defining InterfaceType is possible:
- using the `Andi\GraphQL\Attribute\InterfaceType` attribute ([link](#interface-type-via-attribute));
- by implementing the interface `Andi\GraphQL\Definition\Type\InterfaceTypeInterface` ([link](#interface-type-via-interface)).

## <a id="interface-type-via-attribute">Definition via attribute</a>

To define an InterfaceType, use the `#[InterfaceType]` attribute, this attribute is applicable
to php interfaces and classes:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\InterfaceField;
use Andi\GraphQL\Attribute\InterfaceType;

#[InterfaceType]
interface UserInterface
{
    #[InterfaceField]
    public function getLastname(): string;

    #[InterfaceField]
    public function getFirstname(): string;

    #[InterfaceField]
    public function getDisplayName(): string;
}
```

The `#[InterfaceType]` attribute can contain the following constructor parameters:

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
            The name of the InterfaceType. If not specified, the short name of the php interface/class is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Description of the InterfaceType as displayed in the GraphQL schema.
            If not specified, the php interface/class description specified in docBlock is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>resolveType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>The name of a class that defines a specific implementation of an InterfaceType.</p>
            <p>
                The class must implement the following method:<br />
                <code>__invoke(mixed $value, mixed $context, ResolveInfo $info): ?string</code>
            </p>
            <ul>
                Where:
                <li><code>$value</code> - the data structure being analyzed;</li>
                <li><code>$context</code> - request context;</li>
                <li><code>$info</code> - information about the requested data.</li>
            </ul>
            <p>
                The method must parse the <code>$value</code> structure and return the name of the object
                The GraphQL type associated with this structure. A short object name is allowed
                GraphQL type or the name of a PHP class that implements an ObjectType.
            </p>
            <p>
                If the constructor parameter is not specified, the class will be used by default
                <code>Andi\GraphQL\Common\ResolveType</code>. If the analyzed structure is
                object, <code>ResolveType</code> will try to match the object's class with
                registered GraphQL ObjectType. If matching fails, try again
                will be implemented with the ancestor class (and so on up the inheritance hierarchy).
            </p>
        </td>
    </tr>
</table>

The section [Defining fields of an InterfaceType](interface-field.md) details the capabilities
libraries.

If the `#[InterfaceType]` attribute is applied to a class, then that class may, among other things, implement
auxiliary interface:
- [`ResolveTypeAwareInterface`](#resolve-type-aware-interface) to identify the data structure.

## <a id="interface-type-via-interface">Definition by implementing an interface</a>

> :point_right: **Recommendation!**
>
> Use the abstract class [`Andi\GraphQL\Type\AbstractInterfaceType`](abstract-interface-type.md).
> It already implements the required methods.
>
> The library allows you to define GraphQL types in a way convenient for you.
> At the same time, the created structures can refer to each other.

An example implementation of the `InterfaceTypeInterface` interface:

```php
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
```

The `InterfaceTypeInterface` interface requires the implementation of the following methods:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the InterfaceType.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return a description of the InterfaceType that is mapped to the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="interface-type-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                The method must return an iterable structure (<code>array</code> or
                <code>\Traversable</code>) (an empty structure is not allowed) - list of interface fields
                type.
            </p>
            <p>Each element of the structure can be:</p>
            <ul>
                <li>
                    an instance of the <code>FieldDefinition</code> class
                    (see <a href="https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options">Field configuration options</a>).
                </li>
                <li>
                    an instance of a class that implements the <code>ObjectFieldInterface</code> interface,
                    this is not a typo, it is <code>ObjectFieldInterface</code>
                    (see <a href="object-field.md#object-field-via-interface">Defining fields of an ObjectType by implementing an interface</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

> :point_right: **Note!** :point_left:
>
> The InterfaceType is almost completely identical in its signature to the ObjectType signature.
> List of fields are described using the same interfaces.

### <a id="interface-type-interfaces">Additional Interfaces</a>

<dl>
    <dt><a href="#resolve-type-aware-interface">ResolveTypeAwareInterface</a></dt>
    <dd>
        Allows you to map a data structure to a GraphQL ObjectType.
    </dd>
    <dt><a href="#dynamic-object-type-interface">DynamicObjectTypeInterface</a></dt>
    <dd>
        Allows you to extend an InterfaceType with additional fields defined outside the class.
    </dd>
</dl>


#### <a id="resolve-type-aware-interface">ResolveTypeAwareInterface</a>

The `ResolveTypeAwareInterface` interface requires implementing a method identifying a GraphQL ObjectType,
associated with the analyzed structure (the first parameter of the method).

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

#### <a id="dynamic-object-type-interface">DynamicObjectTypeInterface</a>

Interface `Andi\GraphQL\Type\DynamicObjectTypeInterface` added extensibility
Interface GraphQL generic complex elements known in other classes
(this mechanic is described in detail in [Type expansion](additional-field.md)).

The implementation of this interface affects the definition of the `getFields` method, see the example below:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use App\GraphQL\Field\UserFullName;

final class FullNameAwareInterface implements InterfaceTypeInterface, DynamicObjectTypeInterface
{
    private array $additionalFields = [];

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

        yield from $this->additionalFields;
    }

    public function addAdditionalField(mixed $field): static
    {
        $this->additionalFields[] = $field;

        return $this;
    }
}
```

> :point_right: **Important!**
>
> GraphQL InterfaceType declared with the `#[InterfaceType]` attribute are already extensible.

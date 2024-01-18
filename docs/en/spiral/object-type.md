# Definition of ObjectType

Defining object types is possible:
- using the `Andi\GraphQL\Attribute\ObjectType` attribute ([link](#object-type-via-attribute));
- by implementing the interface `Andi\GraphQL\Definition\Type\ObjectTypeInterface` ([link](#object-type-via-interface)).

## <a id="object-type-via-attribute">Definition using attribute</a>

To define an object type, use the `#[ObjectType]` attribute, this attribute applies to classes:
```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectType;

#[ObjectType]
class User
{
    ...
}
```

The `#[ObjectType]` attribute can contain the following constructor parameters:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">The name of the ObjectType. If not specified, the short name of the class is used.</td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Description of the ObjectType as displayed in the GraphQL schema.
            If not specified, the class description specified in docBlock is used.
        </td>
    </tr>
</table>

The section [Defining GraphQL ObjectType fields](object-field.md) details the capabilities
libraries.

A class with the `#[ObjectType]` attribute can, among other things, implement additional interfaces:
- [`FieldsAwareInterface`](#fields-aware-interface) to define additional fields;
- [`InterfacesAwareInterface`](#interfaces-aware-interface) to define implemented interfaces;
- [`ResolveFieldAwareInterface`](#resolve-field-aware-interface) to determine the resolver,
  used by the default ObjectType;
- [`IsTypeOfAwareInterface`](#is-type-of-aware-interface) to identify the ObjectType.

## <a id="object-type-via-interface">Definition by implementing an interface</a>

> :point_right: **Recommendation!**
>
> Use the abstract class [`Andi\GraphQL\Type\AbstractObjectType`](abstract-object-type.md).
> It already implements the required methods.
>
> The library allows you to define GraphQL types in a way convenient for you.
> At the same time, the created structures can refer to each other.

Example implementation of the `ObjectTypeInterface` interface:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

class Pet implements ObjectTypeInterface
{
    public function getName(): string
    {
        return 'pet';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new Webonyx\FieldDefinition([
            'name' => 'nickname',
            'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
            'resolve' => static fn (string $nickname) => $nickname,
        ]);
    }
}
```

The `ObjectTypeInterface` interface requires the implementation of the following methods:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the ObjectType.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return a description of the ObjectType that is mapped to the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="object-type-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                The method must return an iterable structure (<code>array</code> or
                <code>\Traversable</code>) (an empty structure is not allowed) - a list of object fields
                type.
            </p>
            <p>Each element of the structure can be:</p>
            <ul>
                <li>
                    an instance of the <code>FieldDefinition</code> class
                    (see <a href="https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options">Field configuration options</a>).
                </li>
                <li>
                    an instance of a class that implements the <code>ObjectFieldInterface</code> interface
                    (see <a href="object-field.md#object-field-via-interface">Defining fields of an ObjectType by implementing an interface</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

### <a id="object-type-interfaces">Additional Interfaces</a>

To extend the capabilities of GraphQL ObjectType, you may need to implement interfaces,
listed below.

<dl>
    <dt><a href="#fields-aware-interface">FieldsAwareInterface</a></dt>
    <dd>
        Allows you to define a list of fields of an ObjectType. Interface <code>ObjectTypeInterface</code>
        already implements this interface. Will be useful when expanding ObjectType fields,
        declared using the <code>#[ObjectType]</code> attribute.
    </dd>
    <dt><a href="#interfaces-aware-interface">InterfacesAwareInterface</a></dt>
    <dd>
        Allows you to define a list of <a href="interface-type.md">interface types</a> implemented
        in an ObjectType.
    </dd>
    <dt><a href="#resolve-field-aware-interface">ResolveFieldAwareInterface</a></dt>
    <dd>
        Allows you to specify the default method used in an ObjectType to define
        field values.
    </dd>
    <dt><a href="#is-type-of-aware-interface">IsTypeOfAwareInterface</a></dt>
    <dd>Allows you to determine whether the data being analyzed is of an object type.</dd>
    <dt><a href="#dynamic-object-type-interface">DynamicObjectTypeInterface</a></dt>
    <dd>
        Allows you to extend an ObjectType with additional fields defined outside the class.
    </dd>
</dl>

#### <a id="fields-aware-interface">FieldsAwareInterface</a>

The `FieldsAwareInterface` interface requires the implementation of a single method (it has already been mentioned
[above](#object-type-interface-get-fields)):

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><a id="fields-aware-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                The method must return an iterable structure (<code>array</code> or
                <code>\Traversable</code>) (an empty structure is not allowed).
            </p>
            <p>Each element of the structure can be:</p>
            <ul>
                <li>
                    an instance of the <code>FieldDefinition</code> class
                    (See <a href="https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options">Field configuration options</a>).
                </li>
                <li>
                    an instance of a class that implements the <code>ObjectFieldInterface</code> interface
                    (See <a href="object-field.md#object-field-via-interface">Defining fields of an object type by implementing an interface</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

#### <a id="interfaces-aware-interface">InterfacesAwareInterface</a>

The `Andi\GraphQL\Definition\Type\InterfacesAwareInterface` interface allows you to define a list
interface GraphQL types that your object GraphQL type implements.

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;

#[ObjectType]
class User implements UserInterface, InterfacesAwareInterface
{
    ...

    public function getInterfaces(): iterable
    {
        yield UserInterface::class;
    }
}
```

The `InterfacesAwareInterface` interface requires the implementation of a single method:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getInterfaces</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                The method must return an iterable structure (<code>array</code> or
                <code>\Traversable</code>) (an empty structure is allowed).
            </p>
            <p>
                Each structure element can be:
            </p>
            <ul>
                <li>
                    short name of the InterfaceType
                    (for example: <code>'UserInterface'</code>)
                </li>
                <li>
                    the name of the php class that implements the corresponding InterfaceType
                    (for example: <code>UserInterface::class</code>).
                </li>
            </ul>
        </td>
    </tr>
</table>

#### <a id="resolve-field-aware-interface">ResolveFieldAwareInterface</a>

The `Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface` interface requires a method implementation,
used to calculate the values ​​of fields of an ObjectType (if the field does not have its own resolver):

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;

#[ObjectType]
class User implements UserInterface, ResolveFieldAwareInterface
{
    private array $attributes = [];

    ...

    public function resolveField(mixed $value, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        $field = $info->fieldName;

        return $this->attributes[$field] ?? null;
    }
}
```

The `ResolveFieldAwareInterface` interface requires the implementation of a single method:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>resolveField</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">Must return the value of the field specified in the <code>$info</code> parameter.</td>
    </tr>
</table>

#### <a id="is-type-of-aware-interface">IsTypeOfAwareInterface</a>

The `Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface` interface requires defining a method,
which should return `true` if the value is a GraphQL ObjectType.

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;

#[ObjectType]
class User implements UserInterface, IsTypeOfAwareInterface
{
    ...

    public function isTypeOf(mixed $value, mixed $context, Webonyx\ResolveInfo $info): bool
    {
        return is_object($value) && $value::class === self::class;
    }
}
```

The `IsTypeOfAwareInterface` interface requires the implementation of a single method:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>isTypeOf</code></td>
        <td valign="top"><code>bool</code></td>
        <td valign="top">
            Should return <code>true</code> if the first parameter of the method
            associated with an ObjectType.
        </td>
    </tr>
</table>

This method [will be used](https://webonyx.github.io/graphql-php/type-definitions/interfaces/#interface-role-in-data-fetching)
when identifying a GraphQL ObjectType if the InterfaceType does not contain its own resolver
(in practice, it was not possible to get the logic declared in `webonyx/graphql-php` to work).

#### <a id="dynamic-object-type-interface">DynamicObjectTypeInterface</a>

The `Andi\GraphQL\Type\DynamicObjectTypeInterface` interface adds extensibility
GraphQL ObjectType with additional fields defined in other classes
(this mechanic is described in detail in [Type expansion](additional-field.md)).

The implementation of this interface affects the definition of the `getFields` method, see the example below:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

class Pet implements ObjectTypeInterface, DynamicObjectTypeInterface
{
    private array $additionalFields = [];

    public function getName(): string
    {
        return 'pet';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new Webonyx\FieldDefinition([
            'name' => 'nickname',
            'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
            'resolve' => static fn (string $nickname) => $nickname,
        ]);

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
> ObjectType declared using the `#[ObjectType]` attribute are already extensible.

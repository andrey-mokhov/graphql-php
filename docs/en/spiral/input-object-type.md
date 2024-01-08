# Defining InputObjectType

Defining InputObjectType is possible:
- using the `Andi\GraphQL\Attribute\InputObjectType` attribute ([link](#input-object-type-via-attribute));
- by implementing the interface `Andi\GraphQL\Definition\Type\InputObjectTypeInterface`
  ([link](#input-object-type-via-interface))

## <a id="input-object-type-via-attribute">Definition using an attribute</a>

To define an InputObjectType, use the `#[InputObjectType]` attribute, this attribute
applicable to classes:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\InputObjectType;

#[InputObjectType]
final class CreateUserRequest
{
    ...
}

```

The `#[InputObjectType]` attribute can contain the following constructor parameters:

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
            The name of the InputObjectType. If not specified, the short name of the class is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Description of the InputObjectType as displayed in the GraphQL schema.
            If not specified, the class description specified in docBlock is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="input-object-type-via-attribute-factory"><code>factory</code></a></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                The name of the class factory that transforms incoming data into another structure (for example
                to an instance of the class of the incoming object type).
            </p>
            <p>
                The class must implement the following method:<br />
                <code>__invoke(array $arguments): mixed</code><br />
                where <code>$arguments</code> is an associative array of fields (incoming object type).
            </p>
            <p>
                If the constructor parameter is not specified, the factory will be used:
                <code>Andi\GraphQL\Common\InputObjectFactory</code>
            </p>
        </td>
    </tr>
</table>

Fields of an incoming object type can be set using the `#[InputObjectField]` attribute, about this
is described in detail in [Defining fields of an incoming object type using an attribute](input-object-field.md#input-object-field-via-attribute).

A class with the `#[InputObjectType]` attribute can, among other things, implement auxiliary interfaces:
- [`FieldsAwareInterface`](#fields-aware-interface) to define additional fields;
- [`ParseValueAwareInterface`](#parse-value-aware-interface) to define a method that converts
  incoming data into another structure.

## <a id="input-object-type-via-interface">Definition by implementing an interface</a>

> :point_right: **Recommendation!**
>
> Use the abstract class [`Andi\GraphQL\Type\AbstractInputObjectType`](abstract-input-object-type.md).
> It already implements the required methods.
>
> The library allows you to define GraphQL types in a way convenient for you.
> At the same time, the created structures can refer to each other.

Example implementation of the `InputObjectTypeInterface` interface:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
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
    }
}
```

The `InputObjectTypeInterface` interface requires implementation of the following methods:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the incoming object type.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return a description of the incoming object type that is mapped to the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="input-object-type-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                The method must return an iterable structure (<code>array</code> or
                <code>\Traversable</code>) (an empty structure is not allowed) - list of incoming fields
                object type.
            </p>
            <p>Each structure element must be:</p>
            <ul>
                <li>
                    an instance of the <code>InputObjectField</code> class
                    (see field configuration options, just below <a href="https://webonyx.github.io/graphql-php/type-definitions/inputs/#configuration-options">Configuration options</a>).
                </li>
                <li>
                    an instance of a class that implements the <code>InputObjectFieldInterface</code> interface
                    (See <a href="input-object-field.md#input-object-field-via-interface">Defining fields of an input object type by implementing an interface</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

### <a id="input-object-type-interfaces">Auxiliary Interfaces</a>

To extend the capabilities of InputObjectType, you may need to implement
interfaces listed below.

<dl>
    <dt><a href="#fields-aware-interface">FieldsAwareInterface</a></dt>
    <dd>
        Allows you to define a field list of InputObjectType. Interface
        <code>InputObjectTypeInterface</code> already implements this interface. Will be useful when
        extending fields of an InputObjectType declared using an attribute<br />
        <code>#[InputObjectType]</code>.
    </dd>
    <dt><a href="#parse-value-aware-interface">ParseValueAwareInterface</a></dt>
    <dd>
        Allows you to implement a method that converts incoming data into another structure
        (for example in a DTO object).
    </dd>
</dl>

#### <a id="fields-aware-interface">FieldsAwareInterface</a>

The `FieldsAwareInterface` interface requires the implementation of a single method (it has already been mentioned
[above](#input-object-type-interface-get-fields)):

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
                <code>\Traversable</code>) (an empty structure is not allowed) - field list of
                InputObjectType.
            </p>
            <p>Each element of the structure can be:</p>
            <ul>
                <li>
                    an instance of the <code>InputObjectField</code> class
                    (see field configuration options, just below <a href="https://webonyx.github.io/graphql-php/type-definitions/inputs/#configuration-options">Configuration options</a>).
                </li>
                <li>
                    an instance of a class that implements the <code>InputObjectFieldInterface</code> interface
                    (see <a href="input-object-field.md#input-object-field-via-interface">Defining fields of an InputObjectType by implementing an interface</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

#### <a id="parse-value-aware-interface">ParseValueAwareInterface</a>

The `Andi\GraphQL\Definition\Type\ParseValueAwareInterface` interface requires a static implementation
method used to transform incoming data into another structure (for example, a DTO object):

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;

#[InputObjectType]
final class CreateUserRequest implements ParseValueAwareInterface
{
    public function __construct(
        #[InputObjectField] public readonly string $lastname,
        #[InputObjectField] public readonly string $firstname,
        #[InputObjectField] public readonly string $middlename,
    ) {
    }

    public static function parseValue(array $values): self
    {
        return new self($values['lastname'], $values['firstname'], $values['middlename']);
    }
}
```

The `ParseValueAwareInterface` interface requires the implementation of a single static method:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>parseValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Converts incoming data into a different structure. In the example above - in
            an instance of the <code>CreateUserRequest</code> class.
        </td>
    </tr>
</table>

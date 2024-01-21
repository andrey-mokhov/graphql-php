# Defining fields of an InputObjectType

Defining a field of a GraphQL InputObjectType is possible:
- using the `Andi\GraphQL\Attribute\InputObjectField` attribute ([link](#input-object-field-via-attribute));
- by implementing the interface `Andi\GraphQL\Definition\Field\InputObjectFieldInterface` ([link](#input-object-field-via-interface));

## <a id="input-object-field-via-attribute">Definition via attribute</a>

Defining a field of an InputObjectType using the `#[InputObjectField]` attribute is only possible for
classes<br />marked with the `#[InputObjectType]` attribute. The attribute can be applied to properties and
methods. In this case, the method defining the field must have a single parameter.

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

The `#[InputObjectField]` attribute can have the following constructor parameters:

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
            Field name. If not specified, the property or method name is used without the <code>set</code> prefix
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Description of the field. If not specified, the property/method description is used,
            specified in docBlock. For properties declared in the constructor, as
            descriptions, a comment is used for the corresponding parameter from the docBlock constructor.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>type</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Field type. Valid values ​​can be short names of GraphQL types
                (<code>'String'</code>, <code>'Int'</code>, etc.) or php class names,
                implementing the corresponding GraphQL type (<code>StringType::class</code>,
                <code>IntType::class</code> and others).
            </p>
            <p>
                The field type can be:
                <a href="scalar-type.md"><code>ScalarType</code></a>,
                <a href="enum-type.md"><code>EnumType</code></a>,
                <a href="input-object-type.md"><code>InputObjectType</code></a>.
            </p>
            <p>
                If the <code>type</code> parameter is not specified, the library will try to determine
                value independently (based on the definition of a property or a single parameter
                method). For php types <code>array</code>, <code>iterable</code>, <code>mixed</code>
                etc. you should specify the value of this parameter explicitly.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Field type modifier. The constructor parameter is parsed by the library if
            the field type is specified and does not contain modifiers. The following values ​​are possible:
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Without modifiers, i.e. for example strings or <code>null</code> are acceptable
                    values.<br />
                    Equivalent: <code>String</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED</code></dt>
                <dd>
                    Modifier excluding <code>null</code> value, i.e. the field value will be
                    string.<br />
                    Equivalent: <code>String!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    A modifier defining a list of values ​​(array), with <code>null</code>
                    The field value is valid. So the field value could be:
                    <code>null</code> value, empty array, array with string or
                    <code>null</code> values.<br />
                    Equivalent: <code>[String]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier defining a list of values ​​(array), with <code>null</code>
                    the field value is valid but excluded in values. Thus, the field value
                    could be: <code>null</code> value or non-empty list with strings
                    values.<br />
                    Equivalent: <code>[String!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Combining modifiers using bitwise OR is acceptable.<br />
                    Modifier defining a list of values ​​(array), excluding <code>null</code>
                    field value, but allowing an empty list or a list containing strings or
                    <code>null</code> values.<br />
                    Equivalent: <code>[String]!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier defining a non-empty list of string values ​​(array of strings).<br />
                    Equivalent: <code>[String!]!</code>
                </dd>
            </dl>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            If the parameter is specified, then in the GraphQL schema this field will be marked deprecated. As
            the reason will be indicated by the value of this parameter. If the parameter is not specified, but in
            docBlock (property/method) has a <code>@deprecated</code> tag, then it will be used
            comment for this tag.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>defaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Default field value. Scalar and Enum php values ​​are allowed,
            and also <code>null</code>. If the parameter is not specified, the library will try to determine
            default value yourself (based on the definition of a class property or parameter
            method).
        </td>
    </tr>
</table>

> :point_right: **Note!**
>
> [Default factory](input-object-type.md#input-object-type-via-attribute-factory)
> `Andi\GraphQL\Common\InputObjectFactory` (which instantiates the InputObjectType) is ignored
> the scope of properties/methods, marked with the properties/methods attribute will be called for
> defining the values ​​of the fields.
>
> In this case, the `InputObjectFactory` creates an instance of the class without using a constructor,
> and parameter values ​​will be assigned using reflection, just like calling methods,
> describing the fields of the InputObjectType.

## <a id="input-object-field-via-interface">Definition by implementing an interface</a>

Implementation of the `Andi\GraphQL\Definition\Field\InputObjectFieldInterface` interface may be required
when implementing the `getFields` method required in the interface
[`InputObjectTypeInterface`](input-object-type.md#fields-aware-interface). Which allows you to set
fields for the InputObjectType.

> :point_right: **Recommendation!** :point_left:
>
> To define fields of an InputObjectType instead of implementing an interface
> `InputObjectFieldInterface` use the `Andi\GraphQL\Field\InputObjectField` class, it already contains
> additional interfaces are implemented, and the required values ​​can be set in the constructor.

An example implementation of the `InputObjectFieldInterface` interface (see the `getFields` method):

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
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

        yield new class implements InputObjectFieldInterface {
            public function getName(): string
            {
                return 'password';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function hasDefaultValue(): bool
            {
                return false;
            }

            public function getType(): string
            {
                return 'String';
            }

            public function getMode(): int
            {
                return TypeAwareInterface::IS_REQUIRED;
            }
        };
    }
}
```

The <a id="argument-interface">`InputObjectFieldInterface`</a> interface requires the implementation of the following
methods:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the field as it appears in the GraphQL schema.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return the description of the field as displayed in the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Should return the field type. Valid values ​​can be GraphQL short names
                types (<code>'String'</code>, <code>'Int'</code>, etc.) or php class names,
                implementing the corresponding GraphQL type (<code>StringType::class</code>,
                <code>IntType::class</code> and others).
            </p>
            <p>
                The field type can be:
                <a href="scalar-type.md"><code>ScalarType</code></a>,
                <a href="enum-type.md"><code>EnumType</code></a>,
                <a href="input-object-type.md"><code>InputObjectType</code></a>.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getMode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Should return the bitmask for the field type modifier. The following values ​​are possible:
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Without modifiers, i.e. for example strings or <code>null</code> are acceptable
                    field values.<br />
                    Equivalent: <code>String</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED</code></dt>
                <dd>
                    Modifier excluding <code>null</code> value, i.e. the field value will be
                    string.<br />
                    Equivalent: <code>String!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    A modifier defining a list of values ​​(array), with <code>null</code>
                    the field value is valid. Thus, the field values ​​can be:
                    <code>null</code> value, empty array, array with string or
                    <code>null</code> values.<br />
                    Equivalent: <code>[String]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier defining a list of values ​​(array), with <code>null</code>
                    the field value is valid but excluded in values. Thus, the meanings
                    fields can be: <code>null</code> value or non-empty list with strings
                    values.<br />
                    Equivalent: <code>[String!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Combining modifiers using bitwise OR is acceptable.<br />
                    Modifier defining a list of values ​​(array), excluding <code>null</code>
                    field value, but allowing an empty list or a list containing
                    string or <code>null</code> values.<br />
                    Equivalent: <code>[String]!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier defining a non-empty list of string values ​​(array of strings).<br />
                    Equivalent: <code>[String!]!</code>
                </dd>
            </dl>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>hasDefaultValue</code></td>
        <td valign="top"><code>bool</code></td>
        <td valign="top">
            Should return <code>true</code> if the field has a default value. For determining
            default values ​​should implement the interface <code>DefaultValueAwareInterface</code>
            (see <a href="#default-value-aware-interface">below</a>).
        </td>
    </tr>
</table>

Additional interfaces when defining a field argument:

<dl>
    <dt><a href="#default-value-aware-interface">DefaultValueAwareInterface</a></dt>
    <dd>Allows you to define the default field value.</dd>
    <dt><a href="#deprecation-reason-aware-interface">DeprecationReasonAwareInterface</a></dt>
    <dd>Allows you to specify in the GraphQL schema a reason why a field is not recommended to be used.</dd>
</dl>

### <a id="default-value-aware-interface">DefaultValueAwareInterface</a>

To specify a default value for a field, you must implement the interface
`DefaultValueAwareInterface`, which requires the following method to be implemented:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getDefaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">Should return the default value used by the field.</td>
    </tr>
</table>

### <a id="deprecation-reason-aware-interface">DeprecationReasonAwareInterface</a>

If your GraphQL schema needs to specify reasons why a field is not recommended for use,
it is necessary to implement the `DeprecationReasonAwareInterface` interface, which requires implementation
following method:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getDeprecationReason</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return a description of the reason for displaying in the GraphQL schema for which
            field is not recommended to be used and <code>null</code> value if such a reason
            absent.
        </td>
    </tr>
</table>

> :point_right: **Recommendation!** :point_left:
>
> Using the `InputObjectField` class, the example above would look like this:
>
> ```php
> namespace App\GraphQL\Type;
>
> use Andi\GraphQL\Definition\Field\TypeAwareInterface;
> use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
> use Andi\GraphQL\Field\InputObjectField;
> use GraphQL\Type\Definition as Webonyx;
>
> final class LoginRequest implements InputObjectTypeInterface
> {
>     public function getName(): string
>     {
>         return 'LoginRequest';
>     }
>
>     public function getDescription(): ?string
>     {
>         return null;
>     }
>
>     public function getFields(): iterable
>     {
>         yield new Webonyx\InputObjectField([
>             'name' => 'login',
>             'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
>         ]);
>
>         yield new InputObjectField(
>             name: 'password',
>             type: 'String',
>             mode: TypeAwareInterface::IS_REQUIRED,
>         );
>     }
> }
> ```

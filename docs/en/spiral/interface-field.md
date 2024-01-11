# Defining GraphQL front-end type fields

Defining a field of an interface GraphQL type is possible:
- using the `Andi\GraphQL\Attribute\InterfaceField` attribute ([link](#interface-field-via-attribute));
- by implementing the interface `Andi\GraphQL\Definition\Field\ObjectFieldInterface` ([link](#interface-field-via-interface)).
  This is not a typo when implementing the [`getFields`](interface-type.md#interface-type-interface-get-fields) method
  you can return an instance of a class that implements a given interface;
- using the `Andi\GraphQL\Attribute\AdditionalField` attribute ([link](additional-field.md)).

## <a id="interface-field-via-attribute">Defining fields using an attribute</a>

Defining an interface type field using the `#[InterfaceField]` attribute is only possible for
php interfaces/classes marked with the `#[InterfaceType]` attribute. The attribute can only be applied to
methods.

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

The `#[InterfaceField]` attribute can have the following constructor parameters:

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
            Field name. If not specified, the method name without the <code>get</code> prefix is ​​used
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Description of the field. If not specified, the method description specified in docBlock is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>type</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Field type. Valid values ​​can be short names of GraphQL types
                (<code>'String'</code>, <code>'Int'</code>, etc.) or php class names,
                implementing the corresponding GraphQL type
                (<code>StringType::class</code>, <code>IntType::class</code> and others).
            </p>
            <p>
                The GraphQL field type can be: <a href="scalar-type.md"><code>ScalarType</code></a>,
                <a href="enum-type.md"><code>EnumType</code></a>,
                <a href="object-type.md"><code>ObjectType</code></a>,
                <a href="interface-type.md"><code>InterfaceType</code></a>,
                <a href="union-type.md"><code>UnionType</code></a>.
            </p>
            <p>
                If the <code>type</code> parameter is not specified, the library will try to determine the value
                independently (based on the definition of the method). For php types <code>array</code>,
                <code>iterable</code>, <code>mixed</code>, etc., you should specify the parameter value explicitly.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Field type modifier. The constructor parameter is parsed by the library if
            if the field type is specified and does not contain modifiers. The following values ​​are possible:
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
            docBlock method has a <code>@deprecated</code> tag, then the comment of this tag will be used.
        </td>
    </tr>
</table>

Field arguments can be specified for method parameters with the `#[InterfaceField]` attribute, about this
is detailed in [Field Argument Definition](argument.md#argument-via-attribute).

## <a id="interface-field-via-interface">Defining fields by implementing an interface</a>

As noted [previously](interface-type.md#interface-type-interface-get-fields) interface fields
GraphQL types can be defined by implementing an interface
[`ObjectFieldInterface`](object-field.md#object-field-via-interface).

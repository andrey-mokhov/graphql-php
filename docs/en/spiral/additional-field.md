# Type extension

The mechanism for extend ObjectType is provided by the library initially. For example definition
Query and Mutation of fields using attributes [`#[QueryField]`](query-filed.md№query-field-via-attribute) and
[`#[MutationField]`](mutation-field.md№mutation-field-via-attribute), this is nothing else than an extension of
corresponding object types `Query` and `Mutation`. Thus the attribute
`#[AdditionalField(targetType: 'Query')]` exactly repeats the logic of the attribute<br />
`#[QueryField]`.

Thus, everything said for [`#[QueryField]`](query-filed.md#query-field-via-attribute) is true
for `#[AdditionalField]`.

An example of using the `Andi\GraphQL\Attribute\AdditionalField` attribute:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\Common\ResolverArguments;
use App\GraphQL\Type\User;
use App\GraphQL\Type\UserInterface;

final class UpperCaseName
{
    #[AdditionalField(targetType: User::class)]
    #[AdditionalField(targetType: UserInterface::class)]
    public function upperCaseName(ResolverArguments $arguments): string
    {
        /** @var User $user */
        $user = $arguments->object;

        return strtoupper($user->getDisplayName());
    }
}
```

The `#[AdditionalField]` attribute is applicable to class methods and has the following constructor parameters:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>targetType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                <b>Required parameter</b>. The name of the GraphQL object or interface type to extend.
            </p>
            <p>
                Valid values can be short names of GraphQL object or interface types
                or the names of php classes that implement the corresponding GraphQL type.
            </p>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Field name. If not specified, the method name without the <code>get</code></td> prefix is used
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Description of the field. If not specified, the method description specified in docBlock is used.</td>
    </tr>
    <tr>
        <td valign="top"><code>type</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Field type. Valid values can be short names of GraphQL types
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
                If the <code>type</code> constructor parameter is not specified, the library will try
                determine the value itself (based on the method definitions).
                For php types <code>array</code>, <code>iterable</code>, <code>mixed</code>, etc.
                you must specify the parameter value explicitly.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Field type modifier. The constructor parameter is parsed by the library
            if the field type is specified and does not contain modifiers. The following values are possible:
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
                    A modifier defining a list of values (array), with <code>null</code>
                    the field value is valid. So the field value could be:
                    <code>null</code> value, empty array, array with string or
                    <code>null</code> values.<br />
                    Equivalent: <code>[String]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier defining a list of values (array), with <code>null</code>
                    the field value is valid but excluded in values. Thus, by assigning the field
                    could be: <code>null</code> value or non-empty list with strings
                    values.<br />
                    Equivalent: <code>[String!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Combining modifiers using bitwise OR is acceptable.<br />
                    Modifier defining a list of values (array), excluding <code>null</code>
                    field value, but allowing an empty list or a list containing strings or
                    <code>null</code> values.<br />
                    Equivalent: <code>[String]!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier defining a non-empty list of string values (array of strings).<br />
                    Equivalent: <code>[String!]!</code>
                </dd>
            </dl>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            If the constructor parameter is specified, the field will be marked deprecated in the GraphQL schema.
            The reason will be indicated by the value of this parameter. If the parameter is not specified, but
            in the docBlock of the method there is a tag <code>@deprecated</code>, the comment of this tag will be used.
        </td>
    </tr>
</table>

The additional field arguments are method parameters marked with the php attribute
`Andi\GraphQL\Attribute\Argument`. More details about defining object field arguments can be found in
[Defining an argument using an attribute](argument.md#argument-via-attribute).

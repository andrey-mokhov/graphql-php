# Defining Query Fields

By community agreement, calls to `Query` fields should not lead to changes in system state
(only reading data is allowed). The GraphQL type `Query` is an ObjectType, hence it can be
define in the same way as any other [ObjectType](object-type.md).

The library has a `Query` type, the capabilities of which are sufficient for defining fields.
By default, the class `Andi\GraphQL\Type\QueryType` is used for the `Query` type. Change class,
implementing the `Query` type is possible in [library settings](configure.md).

Defining `Query` fields is possible using:
- php attribute `Andi\GraphQL\Attribute\QueryField`;
- by implementing the `Andi\GraphQL\Field\QueryFieldInterface` interface.

## <a id="query-field-via-attribute">Defining Query fields using an attribute</a>

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Attribute\QueryField;

final class SimpleService
{
    #[QueryField(name: 'echo')]
    #[MutationField(name: 'echo')]
    public function echoMessage(#[Argument] string $message): string
    {
        return 'echo: ' . $message;
    }
}
```

The php attribute `#[QueryField]` is applicable to class methods and has the following constructor parameters:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Field name. If not specified, the method name without prefix is ​​used <code>get</code></td>
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
                Field type. Valid values ​​can be short names of GraphQL types
                (<code>'String'</code>, <code>'Int'</code>, etc.) or php class names,
                implementing the corresponding GraphQL type
                (<code>StringType::class</code>, <code>IntType::class</code> and others).
            </p>
            <p>
                Типом GraphQL поля могут быть: <a href="scalar-type.md"><code>ScalarType</code></a>,
                <a href="enum-type.md"><code>EnumType</code></a>,
                <a href="object-type.md"><code>ObjectType</code></a>,
                <a href="interface-type.md"><code>InterfaceType</code></a>,
                <a href="union-type.md"><code>UnionType</code></a>.
            </p>
            <p>
                If the <code>type</code> constructor parameter is not specified, the library will try
                determine the value yourself (based on the method definitions).
                For php types <code>array</code>, <code>iterable</code>, <code>mixed</code>, etc.
                you must specify the parameter value explicitly.
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
            If the constructor parameter is specified, the field will be marked deprecated in the GraphQL schema.
            The reason will be indicated by the value of this parameter. If the parameter is not specified, but
            in the docBlock of the method there is a tag <code>@deprecated</code>, then the comment will be used
            this tag.
        </td>
    </tr>
</table>

Field arguments are method parameters marked with the php attribute `Andi\GraphQL\Attribute\Argument`.
More details about defining Query field arguments are described in [Defining an argument using an attribute](argument.md#argument-via-attribute).

## <a id="query-field-via-interface">Defining Query fields using the interface</a>

Example implementation of the `QueryFieldInterface` interface:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\MutationFieldInterface;
use Andi\GraphQL\Field\QueryFieldInterface;
use GraphQL\Type\Definition as Webonyx;

final class ConcatService implements QueryFieldInterface, MutationFieldInterface
{
    public function getName(): string
    {
        return 'concat';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getDeprecationReason(): ?string
    {
        return null;
    }

    public function getType(): string
    {
        return Webonyx\StringType::class;
    }

    public function getMode(): int
    {
        return TypeAwareInterface::IS_REQUIRED;
    }
}
```

The `QueryFieldInterface` interface requires the implementation of the following methods:

<table>
    <tr>
        <th>Method name</th>
        <th>Return type</th>
        <th>Method description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the Query field.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return the description of the Query field as displayed in the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
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
    <tr>
        <td valign="top"><code>getType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Should return the data type of the field. Valid values ​​can be short names
                GraphQL types (<code>'String'</code>, <code>'Int'</code>, etc.) or php class names,
                implementing the corresponding GraphQL type (<code>StringType::class</code>,
                <code>IntType::class</code> and others).</p>
            <p>
                The field type can be:
                <a href="scalar-type.md">ScalarType</a>, <a href="enum-type.md">EnumType</a>,
                <a href="object-type.md">ObjectType</a>, <a href="interface-type.md">InterfaceType</a>,
                <a href="union-type.md">UnionType</a>.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="get-type-mode"><code>getMode</code></a></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Should return the bitmask for the field's Query type modifier. The following values ​​are possible:
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
</table>

> :point_right: **Recommendation!**
>
> Use the abstract class `Andi\GraphQL\Field\AbstractObjectField`, it already implements
> the main methods required in the interface.
>
> The example above might look like this:
> ```php
> final class ConcatService extends AbstractObjectField implements QueryFieldInterface, MutationFieldInterface
> {
>     protected string $name = 'concat';
>     protected string $type = Webonyx\StringType::class;
>     protected int $mode = TypeAwareInterface::IS_REQUIRED
> }
> ```

To expand the capabilities of the Query field, you will need to implement additional interfaces:

<dl>
    <dt><a href="object-field.md#arguments-aware-interface"><code>ArgumentsAwareInterface</code></a></dt>
    <dd>
        Allows you to define field arguments.
        In the abstract class <code>AbstractObjectField</code> this interface is already implemented.
    </dd>
    <dt><a href="object-field.md#resolve-aware-interface"><code>ResolveAwareInterface</code></a></dt>
    <dd>Requires a method implementation that calculates the value of the field.</dd>
    <dt><a href="object-field.md#complexity-aware-interface"><code>ComplexityAwareInterface</code></a></dt>
    <dd>
        Allows you to define the <code>complexity</code> method used to limit complexity
        request. More details in the section
        <a href="https://webonyx.github.io/graphql-php/security/#query-complexity-analysis">Security</a>.
    </dd>
</dl>

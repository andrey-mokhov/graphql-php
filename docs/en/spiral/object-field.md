# Defining GraphQL ObjectType fields

To define fields of GraphQL ObjectType the `webonyx/graphql-php` library uses the class
[`FieldDefinion`](https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options).

Defining a field of a GraphQL ObjectType is possible:
- using the `Andi\GraphQL\Attribute\ObjectField` attribute ([link](#object-field-via-attribute));
- by implementing the interface `Andi\GraphQL\Definition\Field\ObjectFieldInterface` ([link](#object-field-via-interface));
- using the `Andi\GraphQL\Attribute\AdditionalField` attribute ([link](additional-field.md)).

## <a id="object-field-via-attribute">Defining fields using attributes</a>

Defining a field of an ObjectType using the `#[ObjectField]` attribute is only possible for classes<br />
marked with the `#[ObjectType]` attribute. The attribute can be applied to properties and methods.

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;

#[ObjectType]
class User
{
    public function __construct(
        private readonly string $lastname,
        private readonly string $firstname,
        #[ObjectField]
        private readonly string $middlename,
    ) {
    }

    #[ObjectField]
    public function getLastname(): string
    {
        return $this->lastname;
    }

    ...
}
```

The `#[ObjectField]` attribute can have the following constructor parameters:

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
            Field name. If not specified, the name of the property or method is used
            without <code>get</code> prefix
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
                If the <code>type</code> parameter is not specified, the library will try to determine the value
                independently (based on the definition of the property/method). For php types
                <code>array</code>, <code>iterable</code>, <code>mixed</code>, etc. should be specified
                the parameter value is explicit.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Field type modifier. The constructor parameter is parsed by the library if
            the field type is specified and does not contain modifiers. The following values are possible:
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
                    the field value is valid but excluded in values. Thus, the field value
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
            If the parameter is specified, then in the GraphQL schema this field will be marked deprecated. As
            the reason will be indicated by the value of this parameter. If the parameter is not specified, but in
            docBlock (property/method) has a <code>@deprecated</code> tag, then it will be used
            comment for this tag.
        </td>
    </tr>
</table>

Field arguments can be specified for method parameters with the `#[ObjectField]` attribute, more on that
outlined in [Field Argument Definition](argument.md#argument-via-attribute).

> :point_right: **Note!**
>
> The library ignores the scope of properties/methods, properties/methods marked with the attribute will
> called to determine the values of fields.


> :point_right: **Important!** :point_left:
>
> To determine the value of fields marked with the `#[ObjectField]` attribute, there must be
> an instance of the class is provided.
>
> For example:
> ```php
> class UserService
> {
>     #[QueryField(type: 'User!')]
>     public function getProfile(): array
>     {
>         // Raise exception:
>         // ReflectionMethod::__construct():
>         //     Argument #1 ($objectOrMethod) must be of type object|string, array given
>         return ['firstname' => 'foo', 'lastname' => 'bar'];
>     }
>
>     #[QueryField]
>     public function getCurrentUser(): User
>     {
>         // correct
>         return new User('Armstrong', 'Neil', 'Alden');
>     }
> }
> ```
>
> However, for fields declared in a different way, there are no such restrictions.

## <a id="object-field-via-interface">Defining a field by implementing an interface</a>

Defining fields using the `ObjectFieldInterface` interface will be required when implementing
interface `FieldsAwareInterface`, this was written about
[here](object-type.md#object-type-interface-get-fields) and
[here](object-type.md#fields-aware-interface-get-fields).

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use App\GraphQL\Field\UserFullName;

#[ObjectType]
class User implements UserInterface, InterfacesAwareInterface, FieldsAwareInterface
{
    ...

    public function getFields(): iterable
    {
        yield new UserFullName();
    }
}
```

> :point_right: **Recommendation!**
>
> Use the abstract class `Andi\GraphQL\Field\AbstractObjectField`, it already implements
> the main methods required in the interface.

Example implementation of the `ObjectFieldInterface` interface:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;

final class UserFullName implements ObjectFieldInterface
{
    public function getName(): string
    {
        return 'fullName';
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
        return 'String';
    }

    public function getMode(): int
    {
        return TypeAwareInterface::IS_REQUIRED;
    }
}
```

The `ObjectFieldInterface` interface requires the implementation of the following methods:

<table>
    <tr>
        <th>Method name</th>
        <th>Return type</th>
        <th>Method description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the field name.</td>
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
                Should return the data type of the field. Valid values can be short names
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
            Should return the bitmask for the field type modifier. The following values are possible:
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
                    the field value is valid but excluded in values. Thus, the field value
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
</table>

To expand the capabilities of the field, the implementation of additional interfaces will be required:

<dl>
    <dt><a href="#arguments-aware-interface"><code>ArgumentsAwareInterface</code></a></dt>
    <dd>
        Allows you to define field arguments.
        In the abstract class <code>AbstractObjectField</code> this interface is already implemented.
    </dd>
    <dt><a href="#resolve-aware-interface"><code>ResolveAwareInterface</code></a></dt>
    <dd>Requires a method implementation that calculates the value of the field.</dd>
    <dt><a href="#complexity-aware-interface"><code>ComplexityAwareInterface</code></a></dt>
    <dd>
        Allows you to define the <code>complexity</code> method used to limit complexity
        request. More details in the section
        <a href="https://webonyx.github.io/graphql-php/security/#query-complexity-analysis">Security</a>.
    </dd>
</dl>

### <a id="arguments-aware-interface">ArgumentsAwareInterface</a>

Example implementation of the `ArgumentsAwareInterface` interface:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;

final class UserFullName implements ObjectFieldInterface, ArgumentsAwareInterface
{
    ...

    public function getArguments(): iterable
    {
        yield new Argument(
            name: 'separator',
            type: 'String',
            mode: TypeAwareInterface::IS_REQUIRED,
            defaultValue: ' ',
        );
    }
}
```

The `ArgumentsAwareInterface` interface requires the following method to be implemented:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getArguments</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>The method must return an iterable structure (<code>array</code> or <code>\Traversable</code>)
            (an empty structure is acceptable - in this case the field will have no arguments).</p>
            <p>The returned structure can contain elements of the following types:</p>
            <dl>
                <dt>Array of argument configuration</dt>
                <dd>
                    The array must meet the argument configuration requirements
                    <code>webonyx/graphql-php</code> libraries. See more details.
                    <a href="https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-argument-configuration-options">official documentation</a>.
                </dd>
                <dt>An object that implements the <code>ArgumentInterface</code></dt> interface
                <dd>
                    In the example above, the <code>Argument</code> class implements the required interface,
                    the requirements of which are set out in section
                    <a href="argument.md#argument-via-interface">Defining an argument using an interface</a>.
                </dd>
            </dl>
        </td>
    </tr>
</table>


### <a id="resolve-aware-interface">ResolveAwareInterface</a>

Example implementation of the `ResolveAwareInterface` interface:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use App\GraphQL\Type\User;
use GraphQL\Type\Definition as Webonyx;

final class UserFullName implements ObjectFieldInterface, ArgumentsAwareInterface, ResolveAwareInterface
{
    ...

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        /** @var User $objectValue */
        return implode(
            $args['separator'],
            [
                $objectValue->getLastname(),
                $objectValue->getFirstname(),
                (new \ReflectionProperty($objectValue, 'middlename'))->getValue($objectValue),
            ],
        );
    }
}
```

The interface requires the implementation of a single `resolve` method. The return value will be
the resulting field value.

The `resolve` method signature matches the requirements for the `resolve` option in
[field configurations](https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options).

### <a id="complexity-aware-interface">ComplexityAwareInterface</a>

An example implementation of the `ComplexityAwareInterface` interface:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ComplexityAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use App\GraphQL\Type\User;
use GraphQL\Type\Definition as Webonyx;

final class UserFullName implements
    ObjectFieldInterface,
    ArgumentsAwareInterface,
    ResolveAwareInterface,
    ComplexityAwareInterface
{
    ...

    public function complexity(int $childrenComplexity, array $args): int
    {
        return $childrenComplexity + 1;
    }
}
```

The interface requires the implementation of a single `complexity` method. Return value of the method
determines the complexity of field calculation.

More details in the [Security](https://webonyx.github.io/graphql-php/security/#query-complexity-analysis) section.

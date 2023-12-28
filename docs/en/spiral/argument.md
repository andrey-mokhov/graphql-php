# Defining Field Arguments

You can define field arguments:
- using the `Andi\GraphQL\Attribute\Argument` attribute ([link](#argument-via-attribute));
- by implementing the interface `Andi\GraphQL\Definition\Field\ArgumentInterface`
  ([link](#argument-via-interface)).

## <a id="argument-via-attribute">Defining an argument using an attribute</a>

The `#[Argument]` attribute applies to method parameters. The method must have one
from the following attributes:
- [`#[QueryField]`](query-filed.md#query-field-via-attribute) - Query type field;
- [`#[MutationField]`](mutation-field.md#mutation-field-via-attribute) - Mutation type field;
- [`#[ObjectField]`](object-field.md#object-field-via-attribute) - ObjectType field;
- [`#[InterfaceField]`](interface-field.md#interface-field-via-attribute) - InterfaceType field;
- [`#[AdditionalField]`](additional-field.md) - defining an additional field for object and interface fields.

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Attribute\QueryField;
use App\GraphQL\Type\User;
use App\GraphQL\Type\UserInterface;

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
The `#[Argument]` attribute can have the following constructor parameters:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Field argument name. If not specified, the parameter name is used.</td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Description of the field argument. If not specified, the parameter description specified
            in the docBlock method is used.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>type</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                The type of the field argument. Valid values ​​can be short names of GraphQL types
                (<code>'String'</code>, <code>'Int'</code>, etc.) or php class names,
                implementing the corresponding GraphQL type (<code>StringType::class</code>,
                <code>IntType::class</code> and others).
            </p>
            <p>
                The argument type can be:
                <a href="scalar-type.md"><code>ScalarType</code></a>,
                <a href="enum-type.md"><code>EnumType</code></a>,
                <a href="input-object-type.md"><code>InputObjectType</code></a>.
            </p>
            <p>
                If the <code>type</code> parameter is not specified, the library will try to determine
                value independently (based on the definition of the parameter). For php types
                <code>array</code>, <code>iterable</code>, <code>mixed</code>, etc. should
                specify the value of this parameter explicitly. Method parameter with spread operator
                (<code>...</code>) will be converted to a list of the appropriate GraphQL type,
                for example: <code>string ...$messages</code> will become <code>[String!]</code>
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Argument type modifier. The constructor parameter is parsed by the library if
            if the argument type is specified and does not contain modifiers. The following values ​​are possible:
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
                    the field value is valid but excluded in values. Thus, by assigning the field
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
            If the constructor parameter is specified, the argument will be marked as deprecated in the GraphQL schema.
            The reason will be the value of this parameter.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>defaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Default argument value. Scalar and Enum php values ​​are allowed,
            and also <code>null</code>. If the parameter is not specified, the library will try to determine
            default value yourself (based on the parameter definition).
        </td>
    </tr>
</table>

> :point_right: **Note!**
>
> When processing a GraphQL request, the corresponding php class method will be called. For parameters with
> the `#[Argument]` attribute will use the values ​​from the GraphQL request, for the remaining parameters
> values ​​from the DI container.
>
> You must be sure that the DI container can determine the values ​​of the method parameters.
>
> ```php
> final class SimpleService
> {
>     #[QueryField(name: 'echo')]
>     public function echoMessage(#[Argument] string $message, LoggerInterface $logger): string
>     {
>         $logger->info('incoming message', ['message' => $message]);
>
>         return 'echo: ' . $message;
>     }
> }
> ```
>
> In this example, the `$logger` parameter will not be displayed in the GraphQL schema, but will be available in the method.

> :point_right: **Note!!!** :point_left:
>
> When calculating the value of a field, you may be asked for information about what parameters the call was called with.
> field resolver (see [configuration option `resolve`](https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options)).
>
> Specify in your method a parameter with type `Andi\GraphQL\Common\ResolverArguments` and you will have access to
> information required:
>
> ```php
> namespace App\GraphQL\Field;
>
> use Andi\GraphQL\Attribute\AdditionalField;
> use Andi\GraphQL\Common\ResolverArguments;
> use App\GraphQL\Type\User;
> use App\GraphQL\Type\UserInterface;
>
> final class UpperCaseName
> {
>     #[AdditionalField(targetType: User::class)]
>     #[AdditionalField(targetType: UserInterface::class)]
>     public function upperCaseName(ResolverArguments $arguments): string
>     {
>         /** @var User $user */
>         $user = $arguments->object;
>
>         return strtoupper($user->getDisplayName());
>     }
> }
> ```
>
> In the example above, the `$arguments` method parameter contains information about the parameters of the resolver call.
>
> ```php
> namespace Andi\GraphQL\Common;
>
> use GraphQL\Type\Definition as Webonyx;
>
> final class ResolverArguments
> {
>     public function __construct(
>         public readonly mixed $object,
>         public readonly array $args,
>         public readonly mixed $context,
>         public readonly Webonyx\ResolveInfo $info,
>     ) {
>     }
> }
> ```

## <a id="argument-via-interface">Defining an Argument Using an Interface</a>

Implementation of the `Andi\GraphQL\Definition\Field\ArgumentInterface` interface may be required
when implementing the `getArguments` method required in the interface
[`ArgumentsAwareInterface`](object-field.md#arguments-aware-interface). Which allows you to set
arguments for fields such as:
- `QueryFieldInterface`
- `MutationFieldInterface`
- `ObjectFieldInterface`


> :point_right: **Recommendation!** :point_left:
>
> To define field arguments instead of implementing the `ArgumentInterface` interface
> use the `Andi\GraphQL\Argument\Argument` class, it already implements auxiliary
> interfaces, and the required values ​​can be set in the constructor.

An example implementation of the `ArgumentInterface` interface (see the `getArguments` method):

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Definition\Field\ArgumentInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\AbstractObjectField;
use Andi\GraphQL\Field\QueryFieldInterface;
use GraphQL\Type\Definition as Webonyx;

final class SquaringService extends AbstractObjectField implements QueryFieldInterface, ResolveAwareInterface
{
    protected string $name = 'square';
    protected string $type = 'Int';
    protected int $mode = TypeAwareInterface::IS_REQUIRED;

    public function getArguments(): iterable
    {
        yield new class implements ArgumentInterface {
            public function getName(): string
            {
                return 'num';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function getType(): string
            {
                return 'Int';
            }

            public function getMode(): int
            {
                return TypeAwareInterface::IS_REQUIRED;
            }

            public function hasDefaultValue(): bool
            {
                return false;
            }
        };
    }

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        return $args['num'] * $args['num'];
    }
}
```

The <a id="argument-interface">`ArgumentInterface`</a> interface requires the implementation of the following methods:

<table>
    <tr>
        <th>Name</th>
        <th>Return type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Should return the name of the argument as it appears in the GraphQL schema.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Should return the description of the argument as displayed in the GraphQL schema.
            Should return <code>null</code> if no description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Must return the type of the argument. Valid values ​​can be GraphQL short names
                types (<code>'String'</code>, <code>'Int'</code>, etc.) or php class names,
                implementing the corresponding GraphQL type (<code>StringType::class</code>,
                <code>IntType::class</code> and others).
            </p>
            <p>
                The argument type can be:
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
            Must return a bitmask for the argument type modifier. The following values ​​are possible:
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Without modifiers, i.e. valid for example numeric or <code>null</code>
                    argument values.<br />
                    Equivalent: <code>Int</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED</code></dt>
                <dd>
                    Modifier excluding <code>null</code> value, i.e. the value of the argument will be
                    number.<br />
                    Equivalent: <code>Int!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    A modifier defining a list of values ​​(array), with <code>null</code>
                    the argument value is valid. Thus, the argument values ​​can be:
                    <code>null</code> value, empty array, array with numeric or
                    <code>null</code> values.<br />
                    Equivalent: <code>[Int]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier defining a list of values ​​(array), with <code>null</code>
                    the argument value is valid, but excluded in values. Thus, the meanings
                    arguments can be: <code>null</code> value or non-empty list with numeric values
                    values.<br />
                    Equivalent: <code>[Int!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Combining modifiers using bitwise OR is acceptable.<br />
                    Modifier defining a list of values ​​(array), excluding <code>null</code>
                    argument value, but allowing an empty list or a list containing
                    numeric or <code>null</code> values.<br />
                    Equivalent: <code>[Int]!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier that defines a non-empty list of numeric values ​​(an array of numbers).<br />
                    Equivalent: <code>[Int!]!</code>
                </dd>
            </dl>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>hasDefaultValue</code></td>
        <td valign="top"><code>bool</code></td>
        <td valign="top">
            Should return <code>true</code> if the argument has a default value. For determining
            default values ​​should implement the interface <code>DefaultValueAwareInterface</code>
            (see <a href="#default-value-aware-interface">below</a>).
        </td>
    </tr>
</table>

Helper interfaces when defining a field argument:

<dl>
    <dt><a href="#default-value-aware-interface">DefaultValueAwareInterface</a></dt>
    <dd>Allows you to specify the default argument value.</dd>
    <dt><a href="#deprecation-reason-aware-interface">DeprecationReasonAwareInterface</a></dt>
    <dd>Allows you to specify in a GraphQL schema a reason why an argument is not recommended to be used.</dd>
</dl>

### <a id="default-value-aware-interface">DefaultValueAwareInterface</a>

To specify a default value for an argument, you must implement the interface
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
        <td valign="top">Must return the value used by the default argument.</td>
    </tr>
</table>

### <a id="deprecation-reason-aware-interface">DeprecationReasonAwareInterface</a>

If your GraphQL schema needs to specify reasons why an argument is not recommended,
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
            argument is not recommended to be used and <code>null</code> value if such a reason
            absent.
        </td>
    </tr>
</table>

> :point_right: **Recommendation!** :point_left:
>
> Using the `Argument` class, the example above would look like this:
> ```php
> namespace App\GraphQL\Field;
>
> use Andi\GraphQL\Argument\Argument;
> use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
> use Andi\GraphQL\Definition\Field\TypeAwareInterface;
> use Andi\GraphQL\Field\AbstractObjectField;
> use Andi\GraphQL\Field\QueryFieldInterface;
> use GraphQL\Type\Definition as Webonyx;
>
> final class SquaringService extends AbstractObjectField implements QueryFieldInterface, ResolveAwareInterface
> {
>     protected string $name = 'square';
>     protected string $type = 'Int';
>     protected int $mode = TypeAwareInterface::IS_REQUIRED;
>
>     public function getArguments(): iterable
>     {
>         yield new Argument(name: 'num', type: 'Int', mode: TypeAwareInterface::IS_REQUIRED);
>     }
>
>     public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
>     {
>         return $args['num'] * $args['num'];
>     }
> }
> ```

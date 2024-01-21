# Abstract class AbstractObjectType

The abstract class `Andi\GraphQL\Type\AbstractObjectType` allows you to define GraphQL object types
without the need to implement methods of auxiliary interfaces. Most interfaces are already
implemented in an abstract class, you just need to set the values ​​of its properties to determine
the result of the implemented methods.

An example of an abstract class implementation:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;
use Andi\GraphQL\Type\AbstractObjectType;
use GraphQL\Type\Definition as Webonyx;

final class ExampleAbstractObjectType extends AbstractObjectType implements ResolveFieldAwareInterface
{
    protected string $name = 'ExampleAbstractObjectType';

    protected iterable $fields = [
        'lastname' => 'String',
        'firstname' => [
            'type' => 'String',
            'mode' => TypeAwareInterface::IS_REQUIRED,
            'description' => 'User firstname',
            'resolve' => [self::class, 'getFirstname'],
        ],
    ];

    protected iterable $interfaces = [ExampleAbstractInterfaceType::class];

    private function getFirstname(User $user): string
    {
        return $user->getFirstname();
    }

    public function resolveField(mixed $value, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        /** @var User $value */
        return match ($info->fieldName) {
            'lastname' => $value->getLastname(),
            default => null,
        };
    }
}
```

When implementing a GraphQL ObjectType using the abstract class `AbstractObjectType` you must
determine the values ​​of the following properties:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>$name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">The name of the ObjectType, <b>required</b> must be defined.</td>
    </tr>
    <tr>
        <td valign="top"><code>$description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Description of the ObjectType as displayed in the GraphQL schema.
            Do not define a value unless a description is required.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$fields</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>List of fields of an ObjectType.</p>
            <p>
                Requirements for elements of an iterable property structure
                <a href="#field-definition">are set out below</a>.
            </p>
            <p>
                It is permissible not to define a property value if you are sure that the object
                the type will be expanded (see <a href="additional-field.md">Type expansion</a>).
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$interfaces</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>List of GraphQL InterfaceType implemented by the ObjectType.</p>
            <p>Each element of the iterable structure can be:</p>
            <ul>
                <li>
                    short name of the GraphQL InterfaceType
                    (for example: <code>'UserInterface'</code>)
                </li>
                <li>
                    the name of the php class that implements the corresponding GraphQL InterfaceType
                    (for example: <code>UserInterface::class</code>).
                </li>
            </ul>
            <p>Do not define a property value unless the ObjectType implements InterfaceType.</p>
        </td>
    </tr>
</table>

### Additional interfaces

A GraphQL ObjectType declared using the abstract class `AbstractObjectType` already implements
[auxiliary interfaces](object-type.md#object-type-interfaces)
[`FieldsAwareInterface`](object-type.md#fields-aware-interface),
[`InterfacesAwareInterface`](object-type.md#interfaces-aware-interface),
[`DynamicObjectTypeInterface`](object-type.md#dynamic-object-type-interface).

To extend the capabilities of a GraphQL ObjectType implemented using an abstract class
`AbstractObjectType`, you may need to implement the following interfaces:

<dl>
    <dt><a href="object-type.md#resolve-field-aware-interface">ResolveFieldAwareInterface</a></dt>
    <dd>
        Allows you to specify the default method used in an ObjectType to define
        field values.
    </dd>
    <dt><a href="object-type.md#is-type-of-aware-interface">IsTypeOfAwareInterface</a></dt>
    <dd>Allows you to determine whether the data being analyzed is of an object type.</dd>
</dl>

## <a id="field-definition">Definition of the iterable structure `$fields`</a>

```php
// Each element of the $fields iterable structure can be:
$this->fields = [
    // an instance of the Webonyx\FieldDefinition class
    new Webonyx\FieldDefinition([...]),

    // an instance of a class that implements the ObjectFieldInterface interface
    new class implements ObjectFieldInterface {...},

    // key => value. This structure is interpreted as follows:
    // key - field name; value - field type.
    'firstname' => 'String',

    // associative array in the form of a field configuration
    'fieldName' => [
        // If the 'name' configuration option is omitted (as in this example), the field name will be
        // structure key used, in this case 'fieldName'.
        // 'name' => 'displayName',

        // Required option that determines the field type. The short name of the GraphQL type is acceptable,
        // or the name of a class that implements the corresponding GraphQL type.
        'type' => 'String',

        // Type modifier.
        'mode' => TypeAwareInterface::IS_REQUIRED,

        // Description of the field used for display in the GraphQL schema. This option is optional.
        'description' => 'Field description',

        // This option is optional. You should define the value of the option if the GraphQL schema
        // you must indicate the reason why this field is not recommended.
        'deprecationReason' => 'This field is deprecated. Don't use it.',

        // Defines a callable structure responsible for calculating the value of the field.
        // May be:
        //   - Closure object
        // - a string like `ClassName::publicStaticMethod`
        // - array of the form ['ClassName', 'publicStaticMethod']
        // - array of the form [$object, 'publicMethod']
        // - array of the form [self::class, 'anyMethod']
        // - array of the form [$this, 'anyMethod']
        // - a string like 'SelfClassName::methodName'
        // - a string like 'methodName'
        //
        // AbstractObjectType ignores the limitation of visibility of methods from the proposed
        // the structure will create a closure that is used to calculate the value of the field.
        //
        // When defining the field's value, the closure will be called with the following parameters:
        // mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info
        'resolve' => [$this, 'methodName'],

        // Defines a callable structure responsible for determining the complexity of data computation.
        // Possible values ​​are the same as the definition of resolve.
        //
        // When determining the complexity of field calculation, the closure will be called with the following parameters:
        // int $childrenComplexity, array $args
        'complexity' => [$this, 'methodName'],

        // List of field arguments. Let's assume iterable data type. This option is optional.
        // Each argument can be:
        'arguments' => [
            // an object that implements the ArgumentInterface interface
            new class implements ArgumentInterface {...},

            // an object that inherits the Webonyx\Type class, and the key will be used as a name
            // argument, and the value is the argument type
            'name' => new Webonyx\Type::string(),

            // key => value. This structure is interpreted as follows:
            // key - argument name; value - argument type.
            'separator' => 'String',

            // associative array as argument configuration
            'argumentName' => [
                // If the 'name' configuration option is omitted (as in this example), as the argument name
                // the structure key will be used, in this case 'argumentName'.
                // 'name' => 'input',

                // Required option that determines the type of the argument. The short name of the GraphQL type is acceptable,
                // or the name of a class that implements the corresponding GraphQL type, or an instance of a class,
                // implementing the abstract class Webonyx\Type.
                'type' => 'String',

                // Type modifier.
                'mode' => TypeAwareInterface::IS_REQUIRED,

                // Description of the argument, used for display in the GraphQL schema. This option is optional.
                'description' => 'Field description',

                // This option is optional. You should define the value of the option if the GraphQL schema
                // you must indicate the reason why this argument is not recommended.
                'deprecationReason' => 'This field is deprecated. Don't use it.',

                // Default argument value. If the argument does not have a default value,
                // do not define this option. because null value is also the default value.
                'defaultValue' => 'hello',
            ],
        ],
    ],
];
```

Total: the protected property `$fields` has an iterable structure. May contain the following elements:
- an instance of the class `Webonyx\FieldDefinition`;
- an instance of a class that implements the `ObjectFieldInterface` interface;
- string `'key' => 'value'`, where the key will be used as the field name, and the value as the field type;
- associative array of field configuration options.

Field configuration options can be as follows:

<table>
    <tr>
        <th>Option</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Field name. If not specified, the key of the iterable structure will be used as the name.
        </td>
    </tr>
    <tr>
        <td valign="top"><b><code>type</code></b></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                <b>Required option</b>that defines the field type.
            </p>
            <p>
                Valid values ​​can be short names of GraphQL types
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
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="fields-type-mode"><code>mode</code></a></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Field type modifier bitmask. The following values ​​are possible:
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
                    line.<br />
                    Equivalent: <code>String!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    A modifier defining a list of values ​​(array), with <code>null</code>
                    The field value is valid. Thus, the field values ​​can be:
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
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Description of the ObjectType field.</td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            If the option is set, then in the GraphQL schema this field will be marked deprecated. As
            The reasons for this will indicate the value of this option.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>resolve</code></td>
        <td valign="top"><code>callable</code> | <code>array</code> | <code>string</code></td>
        <td valign="top">
            <p>
                It is recommended to define a <code>callable</code> structure, even if
                The <code>callable</code> structure is visible only within the class.
            </p>
            <p>It is acceptable to define it as:</p>
            <ul>
                <li>
                    an array simulating a <code>callable</code> structure.<br />
                    For example: <code>[self::class, 'method']</code>, where <code>method</code> is not
                    static method;
                </li>
                <li>
                    string - the name of the class method.
                </li>
            </ul>
            <p>
                The abstract class will try to create a closure from the proposed structure. When calculating
                field values, the closure will be called with the following parameters:
            </p>
            <ul>
                <li><code>mixed $objectValue</code> - structure associated with the ObjectType;</li>
                <li><code>array $args</code> - list of arguments specified in the GraphQL request;</li>
                <li><code>mixed $context</code> - request context;</li>
                <li><code>Webonyx\ResolveInfo $info</code> - information about the requested data.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>complexity</code></td>
        <td valign="top"><code>callable</code> | <code>array</code> | <code>string</code></td>
        <td valign="top">
            <p>
                Valid definitions are similar to the <code>resolve</code> option.
            </p>
            <p>
                The abstract class will try to create a closure from the proposed structure. When determining
                field calculation complexity, the closure will be called with the following parameters:
            </p>
            <ul>
                <li><code>int $childrenComplexity</code> - complexity calculated for child elements;</li>
                <li><code>array $args</code> - list of arguments specified in the GraphQL request.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>arguments</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            List of field arguments. The iterable structure may be empty. Possible configuration methods
            lists of <a href="#field-arguments">arguments are described below</a>.
        </td>
    </tr>
</table>

### <a id="field-arguments">Configuration option `arguments`</a>

The `arguments` option can have an iterable structure. Each element of which can be:
- an instance of the `ArgumentInterface` class;
- an instance of a class that inherits `Webonyx\Type`, where the key determines the name of the argument, and the value its type;
- the form `'key' => 'value'`, where the key will be used as the name of the argument, and the value - the type of the argument;
- an associative array of argument configuration options.

Argument configuration options can be as follows:

<table>
    <tr>
        <th>Option</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Argument name. If not specified, the key of the iterable structure will be used as the name.
        </td>
    </tr>
    <tr>
        <td valign="top"><b><code>type</code></b></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                <b>Required option</b> that specifies the argument type.
            </p>
            <p>
                Valid values ​​can be short names of GraphQL types
                (<code>'String'</code>, <code>'Int'</code>, etc.) or php class names,
                implementing the corresponding GraphQL type
                (<code>StringType::class</code>, <code>IntType::class</code> and others).
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
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Field type modifier bitmask. Possible values ​​for <a href="#fields-type-mode">are described above</a>.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Description of the argument.</td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            If the option is set, the argument will be marked as deprecated in the GraphQL schema. As
            The reasons for this will indicate the value of this option.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>defaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Default argument value. Scalar and Enum php values ​​are allowed,
            and also <code>null</code>.
        </td>
    </tr>
</table>

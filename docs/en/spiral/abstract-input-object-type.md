# Abstract class AbstractInputObjectType

The abstract class Andi\GraphQL\Type\AbstractInputObjectType allows you to define incoming
GraphQL object types without having to implement helper interface methods.
Most interfaces are already implemented in the abstract class, so you just need to define its values,
to determine the results of implemented methods.

An example of an abstract class implementation:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;
use Andi\GraphQL\Type\AbstractInputObjectType;
use GraphQL\Type\Definition\StringType;

final class RegistrationRequest extends AbstractInputObjectType implements ParseValueAwareInterface
{
    protected string $name = 'RegistrationRequest';

    protected iterable $fields = [
        'lastname' => 'String',
        'firstname' => [
            'type' => StringType::class,
            'mode' => TypeAwareInterface::IS_REQUIRED,
        ],
        'middlename' => [
            'type' => 'String',
            'defaultValue' => null,
        ],
    ];

    public static function parseValue(array $values): object
    {
        $object = new \stdClass();
        $object->lastname = $values['lastname'] ?? 'Smith';
        $object->firstname = $values['firstname'];
        $object->middlename = $values['middlename'] ?? 'junior';

        return $object;
    }
}
```

When implementing a GraphQL input object type using the abstract class `AbstractInputObjectType`
you need to define the values ​​of the following properties:

<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <tr>
        <td valign="top"><code>$name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Name of the incoming object type, <b>required</b> must be defined.</td>
    </tr>
    <tr>
        <td valign="top"><code>$description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Description of the incoming object type as displayed in the GraphQL schema.
            Don't define a value unless a description is needed.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$fields</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>List of fields of the incoming object type.</p>
            <p>
                Requirements for elements of an iterable property structure
                <a href="#field-definition">изложены ниже</a>.
            </p>
        </td>
    </tr>
</table>

### Auxiliary Interfaces

A GraphQL input object type, declared using the abstract class `AbstractInputObjectType`,
already implements [auxiliary interface](input-object-type.md#input-object-type-interfaces)
[`FieldsAwareInterface`](input-object-type.md#fields-aware-interface).

To extend the capabilities of an incoming GraphQL object type implemented using an abstract class
`AbstractInputObjectType`, you may need to implement the following interface:

<dl>
    <dt><a href="input-object-type.md#parse-value-aware-interface">ParseValueAwareInterface</a></dt>
    <dd>
        Allows you to implement a method that converts incoming data into another structure
        (for example in a DTO object).
    </dd>
</dl>


## <a id="field-definition">Определение итерируемой структуры `$fields`</a>

```php
// Each element of the iterable structure $fields can be:
$this->fields = [
    // instance of the class Webonyx\InputObjectField
    new Webonyx\InputObjectField([...]),

    // an instance of a class that implements an interface InputObjectFieldInterface
    new class implements InputObjectFieldInterface {...},

    // key => value. This structure is interpreted as follows:
    // key - field name; value - field type.
    'firstname' => 'String',

    // associative array in the form of a field configuration
    'fieldName' => [
        // If the 'name' configuration option is omitted (as in this example), the field name will be
        // the structure key, in this case 'fieldName'.
        // 'name' => 'displayName',

        // A required option that specifies the field type. The short name of the GraphQL type is acceptable,
        // or the name of a class that implements the corresponding GraphQL type.
        'type' => 'String',

        // Type modifier.
        'mode' => TypeAwareInterface::IS_REQUIRED,

        // Description of the field used for display in the GraphQL schema. This option is optional.
        'description' => 'Field description',

        // This option is optional. You should define the value of the option if the GraphQL schema
        // requires to indicate the reason why this field is not recommended.
        'deprecationReason' => 'This field is deprecated. Do not use it.',

        // Default field value. Scalar and Enum php values, as well as null, are allowed.
        // This option is optional.
        'defaultValue' => 'scalar, enum or null value',
    ],
];
```

Conclusion: the protected property `$fields` has an iterable structure. May contain the following elements:
- class instance `Webonyx\InputObjectField`;
- an instance of a class that implements an interface `InputObjectFieldInterface`;
- string `'key' => 'value'`, where the key will be used as the field name and the value as the field type;
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
                (<code>'String'</code>, <code>'Int'</code> и т.д.) or php class names,
                implementing the corresponding GraphQL type
                (<code>StringType::class</code>, <code>IntType::class</code> и другие).
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
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Field type modifier bitmask. Possible values::
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Without modifiers, i.e. valid for example string or <code>null</code>
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
                    field value being valid. Thus, the field values ​​can be:
                    <code>null</code> value, empty array, array with string or
                    <code>null</code> values.<br />
                    Equivalent: <code>[String]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    A modifier defining a list of values ​​(array), with <code>null</code>
                    field value being valid but excluded in values. Thus, the meanings
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
        <td valign="top">Field Description.</td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            If the parameter is specified, the field will be marked obsolete in the GraphQL schema. As
            the reasons for this will indicate the value of this option.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>defaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Default field value. Scalar and Enum php values ​​are allowed,
            and also <code>null</code>.
        </td>
    </tr>
</table>

# Abstract class AbstractEnumType

The abstract class `Andi\GraphQL\Type\AbstractEnumType` allows you to define GraphQL enum types
without the need to implement methods of auxiliary interfaces. Most interfaces are already
implemented in an abstract class, you just need to set the values ​​of its properties to determine
the result of the implemented methods.

An example of an abstract class implementation:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Type\AbstractEnumType;

final class CoinSides extends AbstractEnumType
{
    protected string $name = 'CoinSides';

    protected iterable $values = [
        'heads' => true,
        'tails' => [
            'value' => false,
            'description' => 'Tails of coin',
        ],
    ];
}
```

When implementing a GraphQL enum type using the abstract class `AbstractEnumType` you must
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
        <td valign="top">Enum type name, <b>required</b> must be defined.</td>
    </tr>
    <tr>
        <td valign="top"><code>$description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Enum type description displayed in GraphQL schema.
            Don't define a value unless a description is needed.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$values</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>List of enum items ​​of an enumerated type. <b>required</b> must be defined.</p>sc
            <p>Requirements for elements of an iterable property are below.</p>
        </td>
    </tr>
</table>

## Defining an iterable property `$values`

```php
// Each element of the iterable property $values can be:
$this->values = [
    // an instance of a class that implements an interface EnumValueInterface
    new class implements EnumValueInterface {...},

    // string with a numeric index.
    // In this case, this string will be used as the name and value.
    'red',

    // key => value. This structure is interpreted as follows:
    //   key - possible value name (used in GraphQL queries);
    //   value - the corresponding value used in php.
    // With this configuration:
    // if a possible value was specified in the GraphQL request 'green',
    // in php it will be converted to '#00FF00'.
    // Any data type is acceptable (Including Objects).
    'green' => '#00FF00',

    // an associative array in the form of a configuration of possible values
    'blue' => [
        // If the 'name' configuration option is omitted (as in this example), as the possible name for the value
        // the structure key will be used, in this case 'blue'.
        // 'name' => 'sapphirine',

        // An optional option that specifies an internal (in PHP) value. Any data type is acceptable.
        // If there is no configuration option, the name of the possible value will be used as the value.
        'value' => '#0000FF',

        // Description of a possible value used for display in a GraphQL schema.
        // This option is not required.
        'description' => 'EnumValue description',

        // This option is not required. You should define the value of the option if in the GraphQL schema
        // it is required to indicate the reason why this possible enumeration value is
        // not recomended.
        'deprecationReason' => 'This enum value is deprecated. Do not use it.',
    ],
];
```

Total: protected property `$values`, has an iterable structure. It can contain following elements:
- an instance of a class that implements an interface `EnumValueInterface`;
- `'key' => 'value'`, where the string key will be used as the name of the possible value
  (the name used in GraphQL queries), and the value is the value used in php;
- associative array of configuration options' possible value.

Possible value configuration options can be as follows:

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
            Name of possible value (used in GraphQL queries).
            If not specified, the key of the iterable structure will be used as the name.
        </td>
    </tr>
    <tr>
        <td valign="top"><b><code>value</code></b></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            <p>Optional option that specifies the value available in php.</p>
            <p>If the option is not specified, the name of the possible value will be used.</p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Description of possible value.</td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            If the option is set, then in the GraphQL schema the possible enumeration value will be marked as deprecation.
            The reason will be indicated as the value of this option.
        </td>
    </tr>
</table>

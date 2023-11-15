# Абстрактный класс AbstractInputObjectType

Абстрактный класс `Andi\GraphQL\Type\AbstractInputObjectType` позволяет определять входящие
объектные GraphQL типы без необходимости реализации методов вспомогательных интерфейсов.
Большинство интерфейсов уже реализовано в абстрактном классе, вам достаточно задать значения его
свойств, чтобы определить результат реализованных методов.

Пример реализации абстрактного класса:

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

При реализации входящего объектного GraphQL типа с помощью абстрактного класса `AbstractInputObjectType`
необходимо определить значения следующих свойств:

<table>
    <tr>
        <th>Имя</th>
        <th>Тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>$name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Имя входящего объектного типа, <b>обязательно</b> должно быть определено.</td>
    </tr>
    <tr>
        <td valign="top"><code>$description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание входящего объектного типа, отображаемое в GraphQL схеме.
            Не определяйте значение, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$fields</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>Список полей входящего объектного типа.</p>
            <p>
                Требования к элементам итерируриемой структуры свойства
                <a href="#field-definition">изложены ниже</a>.
            </p>
        </td>
    </tr>
</table>

### Вспомогательные интерфейсы

Входящий объектный GraphQL тип, объявленный с помощью абстрактного класса `AbstractInputObjectType`,
уже реализует [вспомогательный интерфейс](input-object-type.md#input-object-type-interfaces)
[`FieldsAwareInterface`](input-object-type.md#fields-aware-interface).

Для расширения возможностей входящего объектного GraphQL типа, реализованного с помощью абстрактного класса
`AbstractInputObjectType`, вам может потребоваться реализация следующего интерфейса:

<dl>
    <dt><a href="input-object-type.md#parse-value-aware-interface">ParseValueAwareInterface</a></dt>
    <dd>
        Позволяет реализовать метод, осуществляющий преобразование входящих данных в иную структуру
        (например в DTO объект).
    </dd>
</dl>


## <a id="field-definition">Определение итерируемой структуры `$fields`</a>

```php
// Каждый элемент интерируемой структуры $fields может быть:
$this->fields = [
    // экземпляром класса Webonyx\InputObjectField
    new Webonyx\InputObjectField([...]),

    // экземпляром класса, реализующего интерфейс InputObjectFieldInterface
    new class implements InputObjectFieldInterface {...},

    // ключ => значение. Данная структура интерпретируюется следующим образом:
    // ключ - имя поля; значение - тип поля.
    'firstname' => 'String',

    // ассоциативным массивом в виде конфигурации поля
    'fieldName' => [
        // Если опция конфигурации 'name' опущена (как в этом примере), в качестве имени поля будет
        // использован ключ структуры, в данном случае 'fieldName'.
        // 'name' => 'displayName',

        // Обязательная опция, определяющая тип поля. Допустимо краткое имя GraphQL типа,
        // или имя класса, реализующего соответствующий GraphQL тип.
        'type' => 'String',

        // Модификатор типа.
        'mode' => TypeAwareInterface::IS_REQUIRED,

        // Описание поля, используется для отображения в GraphQL схеме. Данная опция не обязательна.
        'description' => 'Field description',

        // Данная опция не обязательна. Следует определить значение опции, если в GraphQL схеме
        // необходимо указать причину по которой данное поле использовать не рекомендуется.
        'deprecationReason' => 'This field is deprecated. Do not use it.',

        // Значение поля по умолчанию. Допустимы скалярные и Enum php-значения, а также null.
        // Данная опция не обязательна.
        'defaultValue' => 'scalar, enum or null value',
    ],
];
```

Итого: защищенное свойство `$fields`, имеет итерируемую структуру. Может содержать следующие элементы:
- экземпляр класса `Webonyx\InputObjectField`;
- экземпляр класса, реализующего интерфейс `InputObjectFieldInterface`;
- строковые `'ключ' => 'значение'`, где ключ будет использован как имя поля, а значение - как тип поля;
- ассоциативный массив опций конфигурации поля.

Опции конфигурации поля могут быть следующими:

<table>
    <tr>
        <th>Опция</th>
        <th>Тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Имя поля. Если не указано, в качестве имени будет использован ключ итерируемой структуры.
        </td>
    </tr>
    <tr>
        <td valign="top"><b><code>type</code></b></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                <b>Обязательная опция</b>, определяющая тип поля.
            </p>
            <p>
                Допустимыми значеними могут быть краткие имена GraphQL типов
                (<code>'String'</code>, <code>'Int'</code> и т.д.) или имена php классов,
                реализующих соответствующий GraphQL тип
                (<code>StringType::class</code>, <code>IntType::class</code> и другие).
            </p>
            <p>
                Типом поля могут быть:
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
            Битовая маска модификатора типа поля. Возможны следующие значения:
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Без модификаторов, т.е. допустимы например строкове или <code>null</code>
                    значения поля.<br />
                    Эквивалент: <code>String</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED</code></dt>
                <dd>
                    Модификатор исключающий <code>null</code> значение, т.е. значением поля будет
                    строка.<br />
                    Эквивалент: <code>String!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение поля допустимо. Таким образом значениями поля могут быть:
                    <code>null</code> значение, пустой массив, массив со строковыми или
                    <code>null</code> значениями.<br />
                    Эквивалент: <code>[String]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение поля допустимо, но исключено в значениях. Таким образом зачениями
                    поля могут быть: <code>null</code> значение или непустой список со строковыми
                    значениями.<br />
                    Эквивалент: <code>[String!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Допустимо объединение модификаторов путем побитового ИЛИ.<br />
                    Модификатор определяющий список значений (массив), исключающий <code>null</code>
                    значение поля, но позволяющий пустой список или список содержащий
                    строковые или <code>null</code> значения.<br />
                    Эквивалент: <code>[String]!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий непустой список строковых значений (массив строк).<br />
                    Эквивалент: <code>[String!]!</code>
                </dd>
            </dl>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Описание поля.</td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Если параметр задан, то в GraphQL схеме поле будет помечено устаревшим. В качестве
            причины будет указано значение данной опции.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>defaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Значение поля по умолчанию. Допустимы скалярные и Enum php-значения,
            а также <code>null</code>.
        </td>
    </tr>
</table>

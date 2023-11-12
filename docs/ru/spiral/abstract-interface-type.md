# Абстрактный класс AbstractInterfaceType

Абстрактный класс `Andi\GraphQL\Type\AbstractInterfaceType` позволяет определять интерфейсные GraphQL типы
без необходимости реализации методов вспомогательных интерфейсов. Большинство интерфейсов уже
реализовано в абстрактном классе, вам достаточно задать значения его свойств, чтобы определить
результат реализованных методов.

Пример реализации абстрактного класса:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Type\AbstractInterfaceType;
use GraphQL\Type\Definition as Webonyx;

final class ExampleAbstractInterfaceType extends AbstractInterfaceType implements ResolveTypeAwareInterface
{
    protected string $name = 'ExampleAbstractInterfaceType';

    protected iterable $fields = [
        'lastname' => 'String',
        'firstname' => [
            'type' => 'String',
            'mode' => TypeAwareInterface::IS_REQUIRED,
            'description' => 'User firstname',
        ],
    ];

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        return match (true) {
            $value instanceof ExampleAbstractObjectType => ExampleAbstractObjectType::class,
            default => null,
        };
    }
}
```

При реализации интерфейсного GraphQL типа с помощью абстрактного класса `AbstractInterfaceType` необходимо
определить значения следующих свойств:

<table>
    <tr>
        <th>Имя</th>
        <th>Тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>$name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Имя интерфейсного типа, <b>обязательно</b> должно быть определено.</td>
    </tr>
    <tr>
        <td valign="top"><code>$description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание интерфейсного типа, отображаемое в GraphQL схеме.
            Не определяйте значение, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$fields</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>Список полей интерфейсного типа.</p>
            <p>
                Требования к элементам итерируриемой структуры свойства
                <a href="#field-definition">изложены ниже</a>.
            </p>
            <p>
                Значение свойства допустимо не определять в том случае, если вы уверены, что интерфейсный
                тип будет расширен (см. <a href="additional-field.md">Раширение типов</a>).
            </p>
        </td>
    </tr>
</table>

Интерфейсный GraphQL тип, объявленный с помощью абстрактного класса `AbstractInterfaceType` уже реализует
[вспомогательный интерфейс](interface-type.md#interface-type-interfaces)
[`DynamicObjectTypeInterface`](interface-type.md#dynamic-object-type-interface).

Для расширения возможностей интерфейсного GraphQL типа, реализованного с помощью абстрактного класса
`AbstractInterfaceType`, вам может потребоваться реализация следующего интерфейса:

<dl>
    <dt><a href="interface-type.md#resolve-type-aware-interface">ResolveTypeAwareInterface</a></dt>
    <dd>
        Позволяет сопоставить структуру данных с объектным GraphQL типом.
    </dd>
</dl>

## <a id="field-definition">Определение итерируемой структуры `$fields`</a>

```php
// Каждый элемент интерируемой структуры $fields может быть:
$this->fields = [
    // экземпляром класса Webonyx\FieldDefinition
    new Webonyx\FieldDefinition([...]),

    // экземпляром класса, реализующего интерфейс ObjectFieldInterface
    new class implements ObjectFieldInterface {...},

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

        // Список аргументов поля. Допустим iterable тип данных. Данная опция не обязательна.
        // Каждый аргумент может быть:
        'arguments' => [
            // объектом, реализующим интерфейс ArgumentInterface
            new class implements ArgumentInterface {...},

            // объектом, наследующий класс Webonyx\Type, при этом ключ будет использован как имя
            // аргумента, а значение - тип аргумента
            'name' => new Webonyx\Type::string(),

            // ключ => значение, Данная структура интерпретируется следующим образом:
            // ключ - имя аргумента; значение - тип аргумента.
            'separator' => 'String',

            // ассоциативным массивом в виде конфигурации аргумента
            'argumentName' => [
                // Если опция конфигурации 'name' опущена (как в этом примере), в качестве имени аргумента
                // будет использован ключ структуры, в данном случае 'argumentName'.
                // 'name' => 'input',

                // Обязательная опция, определяющая тип аргумента. Допустимо краткое имя GraphQL типа,
                // или имя класса, реализующего соответствующий GraphQL тип, либо экземпляр класса,
                // реализующий абстрактный класс Webonyx\Type.
                'type' => 'String',

                // Модификатор типа.
                'mode' => TypeAwareInterface::IS_REQUIRED,

                // Описание аргумента, используется для отображения в GraphQL схеме. Данная опция не обязательна.
                'description' => 'Field description',

                // Данная опция не обязательна. Следует определить значение опции, если в GraphQL схеме
                // необходимо указать причину по которой данный аргумент использовать не рекомендуется.
                'deprecationReason' => 'This field is deprecated. Do not use it.',

                // Значение аргумента, используемое по умолчани. Если аргумент не имеет значения по умолчанию,
                // не определяйте данную опцию. т.к. null значение, это тоже значение по умолчанию.
                'defaultValue' => 'hello',
            ],
        ],
    ],
];
```

Итого: защищенное свойство `$fields`, имеет итерируемую структуру. Может содержать следующие элементы:
- экземпляр класса `Webonyx\FieldDefinition`;
- экземпляр класса, реализующего интерфейс `ObjectFieldInterface`;
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
                Типом GraphQL поля могут быть: <a href="scalar-type.md"><code>ScalarType</code></a>,
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
        <td valign="top">Описание поля объектного типа.</td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Если опция задана, то в GraphQL схеме данное поле будет помечено устаревшим. В качестве
            причины будет указано значение данной опции.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>arguments</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            Список аргументов поля. Итерируемая структура может быть пустой. Возможные способы конфигурации
            списка аргументов <a href="#field-arguments">описаны ниже</a>.
        </td>
    </tr>
</table>

### <a id="field-arguments">Опция конфигурации `arguments`</a>

Опция `arguments` может иметь итерируемую структуру. Каждый элемент которой может быть:
- экземпляром класса `ArgumentInterface`;
- экземпляром класса, наследующего `Webonyx\Type`, где ключ определит имя аргумента, а значение его тип;
- вида `'ключ' => 'значение'`, где ключ будет использован как имя аргумента, а значение - тип аргумента;
- ассоциативный массив опций конфигурации аргумента.

Опции конфигурации аргумента могут быть следующими:

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
            Имя аргумента. Если не указано, в качестве имени будет использован ключ итерируемой структуры.
        </td>
    </tr>
    <tr>
        <td valign="top"><b><code>type</code></b></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                <b>Обязательная опция</b>, определяющая тип аргумента.
            </p>
            <p>
                Допустимыми значеними могут быть краткие имена GraphQL типов
                (<code>'String'</code>, <code>'Int'</code> и т.д.) или имена php классов,
                реализующих соответствующий GraphQL тип
                (<code>StringType::class</code>, <code>IntType::class</code> и другие).
            </p>
            <p>
                Типом аргумента могут быть:
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
            Битовая маска модификатора типа поля. Возможные значения <a href="#fields-type-mode">описаны выше</a>.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Описание аргумента.</td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Если опция задана, то в GraphQL схеме аргумент будет помечен устаревшим. В качестве
            причины будет указано значение данной опции.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>defaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Значение аргумента по умолчанию. Допустимы скалярные и Enum php-значения,
            а также <code>null</code>.
        </td>
    </tr>
</table>

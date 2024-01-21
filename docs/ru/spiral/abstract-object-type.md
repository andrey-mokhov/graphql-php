# Абстрактный класс AbstractObjectType

Абстрактный класс `Andi\GraphQL\Type\AbstractObjectType` позволяет определять объектные GraphQL типы
без необходимости реализации методов вспомогательных интерфейсов. Большинство интерфейсов уже
реализовано в абстрактном классе, вам достаточно задать значения его свойств, чтобы определить
результат реализованных методов.

Пример реализации абстрактного класса:

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

При реализации объектного GraphQL типа с помощью абстрактного класса `AbstractObjectType` необходимо
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
        <td valign="top">Имя объектного типа, <b>обязательно</b> должно быть определено.</td>
    </tr>
    <tr>
        <td valign="top"><code>$description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание объектного типа, отображаемое в GraphQL схеме.
            Не определяйте значение, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$fields</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>Список полей объектного типа.</p>
            <p>
                Требования к элементам итерируемой структуры свойства
                <a href="#field-definition">изложены ниже</a>.
            </p>
            <p>
                Значение свойства допустимо не определять в том случае, если вы уверены, что объектный
                тип будет расширен (см. <a href="additional-field.md">Расширение типов</a>).
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$interfaces</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>Список реализованных объектным типом интерфейсных GraphQL типов.</p>
            <p>Каждый элемент итерируемой структуры может быть:</p>
            <ul>
                <li>
                    кратким именем интерфейсного GraphQL типа
                    (например: <code>'UserInterface'</code>)
                </li>
                <li>
                    именем php класса, реализующего соответствующий интерфейсный GraphQL тип
                    (например: <code>UserInterface::class</code>).
                </li>
            </ul>
            <p>Не определяйте значение свойства, если объектный тип не реализует интерфейсные типы.</p>
        </td>
    </tr>
</table>

### Вспомогательные интерфейсы

Объектный GraphQL тип, объявленный с помощью абстрактного класса `AbstractObjectType` уже реализует
[вспомогательные интерфейсы](object-type.md#object-type-interfaces)
[`FieldsAwareInterface`](object-type.md#fields-aware-interface),
[`InterfacesAwareInterface`](object-type.md#interfaces-aware-interface),
[`DynamicObjectTypeInterface`](object-type.md#dynamic-object-type-interface).

Для расширения возможностей объектного GraphQL типа, реализованного с помощью абстрактного класса
`AbstractObjectType`, вам может потребоваться реализация следующих интерфейсов:

<dl>
    <dt><a href="object-type.md#resolve-field-aware-interface">ResolveFieldAwareInterface</a></dt>
    <dd>
        Позволяет указать метод, используемый в объектном типе по умолчанию для определения
        значений полей.
    </dd>
    <dt><a href="object-type.md#is-type-of-aware-interface">IsTypeOfAwareInterface</a></dt>
    <dd>Позволяет установить, относятся ли анализируемые данные к объектному типу.</dd>
</dl>

## <a id="field-definition">Определение итерируемой структуры `$fields`</a>

```php
// Каждый элемент итерируемой структуры $fields может быть:
$this->fields = [
    // экземпляром класса Webonyx\FieldDefinition
    new Webonyx\FieldDefinition([...]),

    // экземпляром класса, реализующего интерфейс ObjectFieldInterface
    new class implements ObjectFieldInterface {...},

    // ключ => значение. Данная структура интерпретируется следующим образом:
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

        // Определяет callable структуру, ответственную за вычисление значения поля.
        // Может быть:
        //   - Closure объектом
        //   - строка вида `ClassName::publicStaticMethod`
        //   - массив вида ['ClassName', 'publicStaticMethod']
        //   - массив вида [$object, 'publicMethod']
        //   - массив вида [self::class, 'anyMethod']
        //   - массив вида [$this, 'anyMethod']
        //   - строка вида 'SelfClassName::methodName'
        //   - строка вида 'methodName'
        //
        // AbstractObjectType игнорирует ограничение видимости методов, из предложенной
        // структуры будет создано замыкание, которое используется для вычисления значения поля.
        //
        // При определении значения поля замыкание будут вызвано со следующими параметрами:
        // mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info
        'resolve' => [$this, 'methodName'],

        // Определяет callable структуру, ответственную за определение сложности вычисления данных.
        // Возможные значения аналогичны определению resolve.
        //
        // При определении сложности вычисления поля замыкание будут вызвано со следующими параметрами:
        // int $childrenComplexity, array $args
        'complexity' => [$this, 'methodName'],

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

                // Значение аргумента, используемое по умолчанию. Если аргумент не имеет значения по умолчанию,
                // не определяйте данную опцию, т.к. null значение, это тоже значение по умолчанию.
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
                Допустимыми значениями могут быть краткие имена GraphQL типов
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
                    Без модификаторов, т.е. допустимы например строковые или <code>null</code>
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
        <td valign="top"><code>resolve</code></td>
        <td valign="top"><code>callable</code> | <code>array</code> | <code>string</code></td>
        <td valign="top">
            <p>
                Рекомендуется определять <code>callable</code> структуру, даже в том случае, если
                <code>callable</code> структура видна только внутри класса.
            </p>
            <p>Допустимо определять как:</p>
            <ul>
                <li>
                    массив, имитирующий <code>callable</code> структуру.<br />
                    Например: <code>[self::class, 'method']</code>, где <code>method</code> не является
                    статичным методом;
                </li>
                <li>
                    строка - имя метода класса.
                </li>
            </ul>
            <p>
                Абстрактный класс постарается создать замыкание из предложенной структуры. При вычислении
                значения поля замыкание будет вызвано со следующими параметрами:
            </p>
            <ul>
                <li><code>mixed $objectValue</code> - структура, ассоциированная с объектным типом;</li>
                <li><code>array $args</code> - список аргументов, указанных в GraphQL запросе;</li>
                <li><code>mixed $context</code> - контекст запроса;</li>
                <li><code>Webonyx\ResolveInfo $info</code> - информация о запрашиваемых данных.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>complexity</code></td>
        <td valign="top"><code>callable</code> | <code>array</code> | <code>string</code></td>
        <td valign="top">
            <p>
                Допустимы определения аналогичные опции <code>resolve</code>.
            </p>
            <p>
                Абстрактный класс постарается создать замыкание из предложенной структуры. При определении
                сложности вычисления поля замыкание будет вызвано со следующими параметрами:
            </p>
            <ul>
                <li><code>int $childrenComplexity</code> - сложность, рассчитанная для дочерних элементов;</li>
                <li><code>array $args</code> - список аргументов, указанных в GraphQL запросе.</li>
            </ul>
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
                Допустимыми значениями могут быть краткие имена GraphQL типов
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

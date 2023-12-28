# Определение полей интерфейсного GraphQL типа

Определение поля интерфейсного GraphQL типа возможно:
- с помощью атрибута `Andi\GraphQL\Attribute\InterfaceField` ([ссылка](#interface-field-via-attribute));
- путем реализации интерфейса `Andi\GraphQL\Definition\Field\ObjectFieldInterface` ([ссылка](#interface-field-via-interface)).
  Это не опечатка, при реализации метода [`getFields`](interface-type.md#interface-type-interface-get-fields)
  можно возвращать экземпляр класса, реализующий данный интерфейс;
- с помощью атрибута `Andi\GraphQL\Attribute\AdditionalField` ([ссылка](additional-field.md)).

## <a id="interface-field-via-attribute">Определение полей с помощью атрибута</a>

Определить поле интерфейсного типа с помощью атрибута `#[InterfaceField]` возможно только для
php-интерфейсов/классов, помеченных атрибутом `#[InterfaceType]`. Атрибут может быть применен только к
методам.

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\InterfaceField;
use Andi\GraphQL\Attribute\InterfaceType;

#[InterfaceType]
interface UserInterface
{
    #[InterfaceField]
    public function getLastname(): string;

    #[InterfaceField]
    public function getFirstname(): string;

    #[InterfaceField]
    public function getDisplayName(): string;
}
```

Атрибут `#[InterfaceField]` может иметь следующие параметры конструктора:

<table>
    <tr>
        <th>Имя</th>
        <th>Тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Имя поля. Если не указано, используется имя метода без префикса <code>get</code>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание поля. Если не указано, используется описание метода, указанное в docBlock.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>type</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Тип поля. Допустимыми значениями могут быть краткие имена GraphQL типов
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
            <p>
                Если параметр <code>type</code> не указан, библиотека постарается определить значение
                самостоятельно (опираясь на определение метода). Для php типов <code>array</code>,
                <code>iterable</code>, <code>mixed</code> и др. следует указать значение параметра явно.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Модификатора типа поля. Параметр конструктора анализируется библиотекой в том случае,
            если тип поля указан и не содержит модификаторов. Возможны следующие значения:
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Без модификаторов, т.е. допустимы например строковые или <code>null</code>
                    значения.<br />
                    Эквивалент: <code>String</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED</code></dt>
                <dd>
                    Модификатор исключающий <code>null</code> значение, т.е. значение поля будет
                    строковым.<br />
                    Эквивалент: <code>String!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение поля допустимо. Таким образом значением поля может быть:
                    <code>null</code> значение, пустой массив, массив со строковыми или
                    <code>null</code> значениями.<br />
                    Эквивалент: <code>[String]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение поля допустимо, но исключено в значениях. Таким образом значением поля
                    может быть: <code>null</code> значение или непустой список со строковыми
                    значениями.<br />
                    Эквивалент: <code>[String!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Допустимо объединение модификаторов путем побитового ИЛИ.<br />
                    Модификатор определяющий список значений (массив), исключающий <code>null</code>
                    значение поля, но позволяющий пустой список или список содержащий строковые или
                    <code>null</code> значения.<br />
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
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Если параметр задан, то в GraphQL схеме данное поле будет помечено устаревшим. В качестве
            причины будет указано значение данного параметра. Если параметр не задан, но в
            docBlock метода есть тэг <code>@deprecated</code>, то будет использован комментарий этого тега.
        </td>
    </tr>
</table>

Аргументы поля могут быть указаны для параметров метода с атрибутом `#[InterfaceField]`, об этом
подробно изложено в [Определение аргумента поля](argument.md#argument-via-attribute).

## <a id="interface-field-via-interface">Определение полей путем реализации интерфейса</a>

Как уже отмечалось [ранее](interface-type.md#interface-type-interface-get-fields) поля интерфейсного
GraphQL типа могут быть определены путем реализации интерфейса
[`ObjectFieldInterface`](object-field.md#object-field-via-interface).

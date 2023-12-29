# Определение Query полей

По соглашению сообщества, обращения к полям `Query` не должны приводить к изменению состояния системы
(допустимо только чтение данных). GraphQL тип `Query` является объектным типом, следовательно, его можно
определить тем же образом как и любой другой [ObjectType](object-type.md).

В библиотеке подготовлен `Query` тип, возможностей которого достаточно для определения полей.
По умолчанию для `Query` типа используется класс `Andi\GraphQL\Type\QueryType`. Изменить класс,
реализующий тип `Query` возможно в [настройках библиотеки](configure.md).

Определение `Query` полей возможно с помощью:
- php-атрибута `Andi\GraphQL\Attribute\QueryField`;
- путем реализации интерфейса `Andi\GraphQL\Field\QueryFieldInterface`.

## <a id="query-field-via-attribute">Определение Query полей с помощью атрибута</a>

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

php-атрибут `#[QueryField]` применим к методам класса, имеет следующие параметры конструктора:

<table>
    <tr>
        <th>Имя</th>
        <th>Тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Имя поля. Если не указано, используется имя метода без префикса <code>get</code></td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Описание поля. Если не указано, используется описание метода, указанное в docBlock.</td>
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
                Если параметр конструктора <code>type</code> не указан, библиотека постарается
                определить значение самостоятельно (опираясь на определения метода).
                Для php типов <code>array</code>, <code>iterable</code>, <code>mixed</code> и др.
                следует указать значение параметра явно.
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
            Если параметр конструктора задан, то в GraphQL схеме поле будет помечено устаревшим.
            В качестве причины будет указано значение данного параметра. Если параметр не задан, но
            в docBlock метода есть тэг <code>@deprecated</code>, то будет использован комментарий
            этого тега.
        </td>
    </tr>
</table>

Аргументами поля являются параметры метода, помеченные php-атрибутом `Andi\GraphQL\Attribute\Argument`.
Подробнее об определение аргументов Query полей изложено в [Определение аргумента с помощью атрибута](argument.md#argument-via-attribute).

## <a id="query-field-via-interface">Определение Query полей с помощью интерфейса</a>

Пример реализации интерфейса `QueryFieldInterface`:

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

Интерфейс `QueryFieldInterface` требует реализации следующих методов:

<table>
    <tr>
        <th>Имя метода</th>
        <th>Возвращаемый тип</th>
        <th>Описание метода</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен возвращать имя Query поля.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание Query поля, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getDeprecationReason</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен возвращать описание причины, для отображения в GraphQL схеме, по которой
            поле использовать не рекомендуется и <code>null</code> значение, если такая причина
            отсутствует.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Должен вернуть тип данных поля. Допустимыми значениями могут быть краткие имена
                GraphQL типов (<code>'String'</code>, <code>'Int'</code> и т.д.) или имена php классов,
                реализующих соответствующий GraphQL тип (<code>StringType::class</code>,
                <code>IntType::class</code> и другие).</p>
            <p>
                Типом поля могут быть:
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
            Должен вернуть битовую маску для модификатора типа Query поля. Возможны следующие значения:
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
</table>

> :point_right: **Рекомендация!**
>
> Воспользуйтесь абстрактным классом `Andi\GraphQL\Field\AbstractObjectField`, в нём уже реализованы
> основные методы требуемые в интерфейсе.
>
> Пример выше мог бы выглядеть так:
> ```php
> final class ConcatService extends AbstractObjectField implements QueryFieldInterface, MutationFieldInterface
> {
>     protected string $name = 'concat';
>     protected string $type = Webonyx\StringType::class;
>     protected int $mode = TypeAwareInterface::IS_REQUIRED
> }
> ```

Для расширения возможностей Query поля потребуются реализация дополнительных интерфейсов:

<dl>
    <dt><a href="object-field.md#arguments-aware-interface"><code>ArgumentsAwareInterface</code></a></dt>
    <dd>
        Позволяет определить аргументы поля.
        В абстрактном классе <code>AbstractObjectField</code> данный интерфейс уже реализован.
    </dd>
    <dt><a href="object-field.md#resolve-aware-interface"><code>ResolveAwareInterface</code></a></dt>
    <dd>Потребует реализацию метода, вычисляющую значение поля.</dd>
    <dt><a href="object-field.md#complexity-aware-interface"><code>ComplexityAwareInterface</code></a></dt>
    <dd>
        Позволяет определить метод <code>complexity</code>, используемый для ограничения сложности
        запроса. Подробнее в разделе
        <a href="https://webonyx.github.io/graphql-php/security/#query-complexity-analysis">Security</a>.
    </dd>
</dl>

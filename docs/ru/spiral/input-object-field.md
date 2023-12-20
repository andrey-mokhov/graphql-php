# Определение полей входящего объектного GraphQL типа

Определение поля объектного GraphQL типа возможно:
- с помощью атрибута `Andi\GraphQL\Attribute\InputObjectField` ([ссылка](#input-object-field-via-attribute));
- путем реализации интерфейса `Andi\GraphQL\Definition\Field\InputObjectFieldInterface` ([ссылка](#input-object-field-via-interface));

## <a id="input-object-field-via-attribute">Определение с помощью атрибута</a>

Определить поле входящего объектного типа с помощью атрибута `#[InputObjectField]` возможно только для
классов,<br />помеченных атрибутом `#[InputObjectType]`. Атрибут может быть применен к свойствам и
методам. При этом, метод, определяющий поле, должен иметь единственный параметр.

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;

#[InputObjectType]
final class CreateUserRequest implements ParseValueAwareInterface
{
    public function __construct(
        #[InputObjectField] public readonly string $lastname,
        #[InputObjectField] public readonly string $firstname,
        #[InputObjectField] public readonly string $middlename,
    ) {
    }

    public static function parseValue(array $values): self
    {
        return new self($values['lastname'], $values['firstname'], $values['middlename']);
    }
}
```

Атрибут `#[InputObjectField]` может иметь следующие параметры конструктора:

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
            Имя поля. Если не указано, используется имя свойства или метода без префикса <code>set</code>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание поля. Если не указано, используется описание свойства/метода,
            указанное в docBlock. Для свойств, объявленных в конструкторе, в качестве
            описания используется комментарий к соответствующему параметру из docBlock конструктора.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>type</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Тип поля. Допустимыми значеними могут быть краткие имена GraphQL типов
                (<code>'String'</code>, <code>'Int'</code> и т.д.) или имена php классов,
                реализующих соответствующий GraphQL тип (<code>StringType::class</code>,
                <code>IntType::class</code> и другие).
            </p>
            <p>
                Типом поля могут быть:
                <a href="scalar-type.md"><code>ScalarType</code></a>,
                <a href="enum-type.md"><code>EnumType</code></a>,
                <a href="input-object-type.md"><code>InputObjectType</code></a>.
            </p>
            <p>
                Если параметр <code>type</code> не указан, библиотека постарается определить
                значение самостоятельно (опираясь на определение свойства или единственного параметра
                метода). Для php типов <code>array</code>, <code>iterable</code>, <code>mixed</code>
                и др. следует указать значение данного параметра явно.
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
                    значение поля допустимо, но исключено в значениях. Таким образом зачением поля
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
            docBlock (свойства/метода) есть тэг <code>@deprecated</code>, то будет использован
            комментарий этого тэга.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>defaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Значение поля по умолчанию. Допустимы скалярные и Enum php-значения,
            а также <code>null</code>. Если параметр не задан, библиотека постарается определить
            значение по умолчанию самостоятельно (опираясь на определение свойства класса или параметра
            метода).
        </td>
    </tr>
</table>

> :point_right: **Обратите внимание!**
>
> [Используемая по умолчанию фабрика](input-object-type.md#input-object-type-via-attribute-factory)
> `Andi\GraphQL\Common\InputObjectFactory` (создающая экземпляр входящего объектного типа) игнорируют
> область видимости свойств/методов, помеченные атрибутом свойства/методы будут вызваны для
> определения значения полей.
>
> При этом фабрика `InputObjectFactory` создает экземпляр класса без использования конструктор,
> а значения параметров будут присвоены с использованием рефлексии, как и вызов методов,
> описывающих поля входящего объектного типа.

## <a id="input-object-field-via-interface">Определение путем реализации интерфейса</a>

Реализация интерфейса `Andi\GraphQL\Definition\Field\InputObjectFieldInterface` может потребоваться
при реализации метода `getFields`, требуемого в интерфейсе
[`InputObjectTypeInterface`](input-object-type.md#fields-aware-interface). Который позволяет задать
поля для входящего объектного типа.

> :point_right: **Рекомендация!** :point_left:
>
> Для определения полей входящего объектного типа вместо реализации интерфейса
> `InputObjectFieldInterface` используйте класс `Andi\GraphQL\Field\InputObjectField`, в нём уже
> реализованы вспомогательные интерфейсы, а требуемые значения можно задать в конструкторе.

Пример реализации интерфейса `InputObjectFieldInterface` (см. метод `getFields`):

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\InputObjectFieldInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

final class LoginRequest implements InputObjectTypeInterface
{
    public function getName(): string
    {
        return 'LoginRequest';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new Webonyx\InputObjectField([
            'name' => 'login',
            'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
        ]);

        yield new class implements InputObjectFieldInterface {
            public function getName(): string
            {
                return 'password';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function hasDefaultValue(): bool
            {
                return false;
            }

            public function getType(): string
            {
                return 'String';
            }

            public function getMode(): int
            {
                return TypeAwareInterface::IS_REQUIRED;
            }
        };
    }
}
```

Интерфейс <a id="argument-interface">`InputObjectFieldInterface`</a> требует реализации следующих
методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен возращать имя поля, отображаемое в GraphQL схеме.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание поля, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Должен вернуть тип поля. Допустимыми значеними могут быть краткие имена GraphQL
                типов (<code>'String'</code>, <code>'Int'</code> и т.д.) или имена php классов,
                реализующих соответствующий GraphQL тип (<code>StringType::class</code>,
                <code>IntType::class</code> и другие).
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
        <td valign="top"><code>getMode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Должен вернуть битовую маску для модификатора типа поля. Возможны следующие значения:
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
        <td valign="top"><code>hasDefaultValue</code></td>
        <td valign="top"><code>bool</code></td>
        <td valign="top">
            Должен вернуть <code>true</code> если поле имеет значение по умолчанию. Для определения
            значения по умолчанию следует реализовать интерфейс <code>DefaultValueAwareInterface</code>
            (см. <a href="#default-value-aware-interface">ниже</a>).
        </td>
    </tr>
</table>

Вспомогательные интерфейсы при определении аргумента поля:

<dl>
    <dt><a href="#default-value-aware-interface">DefaultValueAwareInterface</a></dt>
    <dd>Позволяет определить значение поля, используемого по умолчанию.</dd>
    <dt><a href="#deprecation-reason-aware-interface">DeprecationReasonAwareInterface</a></dt>
    <dd>Позволяет в GraphQL схеме указать причину, по которой поле использовать не рекомендуется.</dd>
</dl>

### <a id="default-value-aware-interface">DefaultValueAwareInterface</a>

Чтобы указать для поля значение по умолчанию следует реализовать интерфейс
`DefaultValueAwareInterface`, который требует реализации следующего метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getDefaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">Должен возращать значение, используемое полем по умолчанию.</td>
    </tr>
</table>

### <a id="deprecation-reason-aware-interface">DeprecationReasonAwareInterface</a>

Если в GraphQL схеме необходимо указать причины, по которой поле не рекомендуется использовать,
необходимо реализовать интерфейс `DeprecationReasonAwareInterface`, который требует реализации
следующего метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
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
</table>

> :point_right: **Рекомендация!** :point_left:
>
> При использовании класса `InputObjectField` пример выше выглядел бы следующим образом:
>
> ```php
> namespace App\GraphQL\Type;
>
> use Andi\GraphQL\Definition\Field\TypeAwareInterface;
> use Andi\GraphQL\Definition\Type\InputObjectTypeInterface;
> use Andi\GraphQL\Field\InputObjectField;
> use GraphQL\Type\Definition as Webonyx;
>
> final class LoginRequest implements InputObjectTypeInterface
> {
>     public function getName(): string
>     {
>         return 'LoginRequest';
>     }
>
>     public function getDescription(): ?string
>     {
>         return null;
>     }
>
>     public function getFields(): iterable
>     {
>         yield new Webonyx\InputObjectField([
>             'name' => 'login',
>             'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
>         ]);
>
>         yield new InputObjectField(
>             name: 'password',
>             type: 'String',
>             mode: TypeAwareInterface::IS_REQUIRED,
>         );
>     }
> }
> ```

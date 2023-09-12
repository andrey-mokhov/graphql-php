# Определение InputObjectType

Определение входящих объектных типов возможно:
- с помощью атрибута `Andi\GraphQL\Attribute\InputObjectType` ([ссылка](#input-object-type-via-attribute));
- путем реализации интерфейса `Andi\GraphQL\Definition\Type\InputObjectTypeInterface`
  ([ссылка](#input-object-type-via-interface))

## <a id="input-object-type-via-attribute">Определение с помощью атрибута</a>

Для определение входящего объектного типа используйте атрибута `#[InputObjectType]`, данный атрибут
применим к классам:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\InputObjectType;

#[InputObjectType]
final class CreateUserRequest
{
    ...
}

```

Атрибут `#[InputObjectType]` может содержать следующие параметры конструктора:

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
            Имя входящего объектного типа. Если не указано, используется краткое имя класса.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание входящего объектного типа, отображаемое в GraphQL схеме.
            Если не указано, используется описание класса, указанное в docBlock.
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="input-object-type-via-attribute-factory"><code>factory</code></a></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Имя класса, преобразующего входящие данные в иную структуру (например
                в экземпляр класса входящего объектного типа).
            </p>
            <p>
                Класс должен реализовывать следующий метод:<br />
                <code>__invoke(array $arguments): mixed</code><br />
                где <code>$arguments</code> - ассоциативный массив полей (входящего объектного типа).
            </p>
            <p>
                Если параметр конструктора не задан, будет использована фабрика:
                <code>Andi\GraphQL\Common\InputObjectFactory</code>
            </p>
        </td>
    </tr>
</table>

Поля входящего объектного типа можно задать с помошью атрибута `#[InputObjectField]`, об этом
подробно изложено в [Определение полей входящего объектного типа с помощью атрибута](input-object-field.md#input-object-field-via-attribute).

Класс с атрибутом `#[InputObjectType]` может, в том числе, реализовывать вспомогательные интерфейсы:
- [`FieldsAwareInterface`](#fields-aware-interface) для определения дополнительных полей;
- [`ParseValueAwareInterface`](#parse-value-aware-interface) для определения метода, преобразующего
  входящие данные в иную структуру.

## <a id="input-object-type-via-interface">Определение путем реализации интерфейса</a>

> :point_right: **Рекомендация!**
>
> Воспользуйтесь абстрактным классом [`Andi\GraphQL\Type\AbstractInputObjectType`](abstract-input-object-type.md).
> В нём уже реализованы требуемые методы.
>
> Библиотека позволяет определять GraphQL типы удобным для вас способом.
> При этом, созданные структуры могут ссылаться друг на друга.

Пример реализации интерфейса `InputObjectTypeInterface`:

```php
namespace App\GraphQL\Type;

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
    }
}
```

Интерфейс `InputObjectTypeInterface` требует реализации следующих методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен вернуть имя входящего объектного типа.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание входящего объектного типа, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="input-object-type-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Метод должен возвращать итерируемую структуру (<code>array</code> или
                <code>\Traversable</code>) (пустая структура недопустима) - список полей входящего
                объектного типа.
            </p>
            <p>Каждый элемент структуры может быть:</p>
            <ul>
                <li>
                    экземпляром класса <code>InputObjectField</code>
                    (см. опции кофигурации поля, чуть ниже <a href="https://webonyx.github.io/graphql-php/type-definitions/inputs/#configuration-options">Configuration options</a>).
                </li>
                <li>
                    экземпляром класса, реализующего интерфейс <code>InputObjectFieldInterface</code>
                    (см. <a href="input-object-field.md#input-object-field-via-interface">Определение полей входящего объектного типа путем реализации интерфейса</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

### Вспомогательные интерфейсы

Для расширения возможностей входящих объектных GraphQL типов, вам может потребоваться реализация
интерфейсов, перечисленных ниже.

<dl>
    <dt><a href="#fields-aware-interface">FieldsAwareInterface</a></dt>
    <dd>
        Позволяет определить список полей входящего объектного типа. Интерфейс
        <code>InputObjectTypeInterface</code> уже реализует данный интерфейс. Будет полезен при
        расширении полей входящего объектного типа, объявленного с помощью атрибута<br />
        <code>#[InputObjectType]</code>.
    </dd>
    <dt><a href="#parse-value-aware-interface">ParseValueAwareInterface</a></dt>
    <dd>
        Позволяет реализовать метод, осуществляющий преобразование входящих данных в иную структуру
        (например в DTO объект).
    </dd>
</dl>

#### <a id="fields-aware-interface">FieldsAwareInterface</a>

Интерфейс `FieldsAwareInterface` требует реализацию единственного метода (о нём уже упоминалось
[выше](#input-object-type-interface-get-fields)):

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><a id="fields-aware-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Метод должен возвращать итерируемую структуру (<code>array</code> или
                <code>\Traversable</code>) (пустая структура недопустима) - список полей входящего
                объектного типа.
            </p>
            <p>Каждый элемент структуры может быть:</p>
            <ul>
                <li>
                    экземпляром класса <code>InputObjectField</code>
                    (см. опции кофигурации поля, чуть ниже <a href="https://webonyx.github.io/graphql-php/type-definitions/inputs/#configuration-options">Configuration options</a>).
                </li>
                <li>
                    экземпляром класса, реализующего интерфейс <code>InputObjectFieldInterface</code>
                    (см. <a href="input-object-field.md#input-object-field-via-interface">Определение полей входящего объектного типа путем реализации интерфейса</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

#### <a id="parse-value-aware-interface">ParseValueAwareInterface</a>

Интерфейс `Andi\GraphQL\Definition\Type\ParseValueAwareInterface` требует реализации статичного
метода, используемого для преобразования входящих данных в иную структуру (например в DTO объект):

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

Интерфейс `ParseValueAwareInterface` требует реализации единственного статичного метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>parseValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Осуществляет преобразование входящих данных в иную структуру. В примере выше - в
            экземпляр класса <code>CreateUserRequest</code>.
        </td>
    </tr>
</table>

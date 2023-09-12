# Определение InterfaceType

Определение интерфейсных типов возможно:
- с помощью атрибута `Andi\GraphQL\Attribute\InterfaceType` ([ссылка](#interface-type-via-attribute));
- путем реализации интерфейса `Andi\GraphQL\Definition\Type\InterfaceTypeInterface` ([ссылка](#interface-type-via-interface)).

## <a id="interface-type-via-attribute">Определение с помощью атрибута</a>

Для определения интерфейсного типа используйте атрибут `#[InterfaceType]`, данный атрибут применим
к php-интерфейсам и классам:

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

Атрибут `#[InterfaceType]` может содержать следующие параметры конструктора:

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
            Имя интерфейсного типа. Если не указано, используется краткое имя php-интерфейса/класса.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание интерфейсного типа, отображаемое в GraphQL схеме.
            Если не указано, используется описание php-интерфейса/класса, указанное в docBlock.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>resolveType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>Имя класса, определяющий конкретную реализацию интерфейсного типа.</p>
            <p>
                Класс должен реализовывать следующий метод:<br />
                <code>__invoke(mixed $value, mixed $context, ResolveInfo $info): ?string</code>
            </p>
            <ul>
                где:
                <li><code>$value</code> - анализируемая структура данных;</li>
                <li><code>$context</code> - контекст запроса;</li>
                <li><code>$info</code> - информация о запрашиваемых данных.</li>
            </ul>
            <p>
                Метод должен проанализировать структуру <code>$value</code> и вернуть имя объектного
                GraphQL типа, ассоциированного с этой структурой. Допустимо краткое имя объектного
                GraphQL типа или имя php-класса, реализующего объектный тип.
            </p>
            <p>
                Если параметр конструктора не указан, по умолчанию будет использован класс
                <code>Andi\GraphQL\Common\ResolveType</code>. Если анализируемая структура является
                объектом, <code>ResolveType</code> попробует сопоставить класс объекта с
                зарегистрированным объектным GraphQL типом. При неудачном сопоставлении повторная попытка
                будет осуществлена с классом предка (и т.д. вверх по иерархии наследования).
            </p>
        </td>
    </tr>
</table>

В разделе [Определение полей интерфейсного GraphQL типа](interface-field.md) подробно изложены возможности
библиотеки.

Если атрибут `#[InterfaceType]` применен к классу, то этот класс может, в том числе, реализовать
вспомогательный интерфейс:
- [`ResolveTypeAwareInterface`](#resolve-type-aware-interface) для идентификации структуры данных.

## <a id="interface-type-via-interface">Определение путем реализации интерфейса</a>

> :point_right: **Рекомендация!**
>
> Воспользуйтесь абстрактным классом [`Andi\GraphQL\Type\AbstractInterfaceType`](abstract-interface-type.md).
> В нём уже реализованы требуемые методы.
>
> Библиотека позволяет определять GraphQL типы удобным для вас способом.
> При этом, созданные структуры могут ссылаться друг на друга.

Пример реализации интерфейса `InterfaceTypeInterface`:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use App\GraphQL\Field\UserFullName;

final class FullNameAwareInterface implements InterfaceTypeInterface
{
    public function getName(): string
    {
        return 'FullNameAwareInterface';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new UserFullName();
    }
}
```

Интерфейс `InterfaceTypeInterface` требует реализации следующих методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен вернуть имя интерфейсного типа.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание интерфейсного типа, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="interface-type-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Метод должен возвращать итерируемую структуру (<code>array</code> или
                <code>\Traversable</code>) (пустая структура недопустима) - список полей интерфейсного
                типа.
            </p>
            <p>Каждый элемент структуры может быть:</p>
            <ul>
                <li>
                    экземпляром класса <code>FieldDefinition</code>
                    (см. <a href="https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options">Field configuration options</a>).
                </li>
                <li>
                    экземпляром класса, реализующего интерфейс <code>ObjectFieldInterface</code>,
                    это не опечатка, имеено <code>ObjectFieldInterface</code>
                    (см. <a href="object-field.md#object-field-via-interface">Определение полей объектного типа путем реализации интерфейса</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

> :point_right: **Обратите внимание!** :point_left:
>
> Интерфейсный тип по своей сигнатуре практически полностью совпадает в сигнатурой объектного типа.
> Список полей описываются с использованием тех же интерфейсов.

### Вспомогательные интерфейсы

<dl>
    <dt><a href="#resolve-type-aware-interface">ResolveTypeAwareInterface</a></dt>
    <dd>
        Позволяет сопоставить структуру данных с объектным GraphQL типом.
    </dd>
    <dt><a href="#dynamic-object-type-interface">DynamicObjectTypeInterface</a></dt>
    <dd>
        Позволяет расширять интерфейсный тип дополнительными полями, опреденными вне класса.
    </dd>
</dl>


#### <a id="resolve-type-aware-interface">ResolveTypeAwareInterface</a>

Интерфейс `ResolveTypeAwareInterface` требует реализовать метод идентифицирующий объектный GraphQL тип,
ассоциированный с анализируемой структурой (первым параметром метода).

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>resolveType</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Метод должен проанализировать структуру первого параметра <code>$value</code> и вернуть
            имя объектного GraphQL типа, ассоциированного с этой структурой. Допустимо краткое имя
            объектного GraphQL типа или имя php-класса, реализующего cсоответствующий объектный тип.
        </td>
    </tr>
</table>

#### <a id="dynamic-object-type-interface">DynamicObjectTypeInterface</a>

Интерфейс `Andi\GraphQL\Type\DynamicObjectTypeInterface` добавляет возможность расширения
интерфейсных GraphQL типов дополнительными полями, определенных в иных классах
(данная механика подробно описана в [Расширение типов](additional-field.md)).

Реализация данного интерфейса влияет на определение метода `getFields`, см. пример ниже:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\InterfaceTypeInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use App\GraphQL\Field\UserFullName;

final class FullNameAwareInterface implements InterfaceTypeInterface, DynamicObjectTypeInterface
{
    private array $additionalFields = [];

    public function getName(): string
    {
        return 'FullNameAwareInterface';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new UserFullName();

        yield from $this->additionalFields;
    }

    public function addAdditionalField(mixed $field): static
    {
        $this->additionalFields[] = $field;

        return $this;
    }
}
```

> :point_right: **Важно!**
>
> Интерфейсные GraphQL типы объявленные с помощью атрибута `#[InterfaceType]` уже являются расширяемыми.

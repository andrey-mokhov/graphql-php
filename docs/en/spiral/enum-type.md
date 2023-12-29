# Определение EnumType

Определение перечислимых типов возможно:
- с помощью атрибута `Andi\GraphQL\Attribute\EnumType` ([ссылка](#enum-type-via-attribute));
- путем реализации интерфейса `Andi\GraphQL\Definition\Type\EnumTypeInterface` ([ссылка](#enum-type-via-interface)).

## <a id="enum-type-via-attribute">Определение с помощью атрибута</a>

Для определения перечислимого типа используйте атрибут `#[EnumType]`, данный атрибут применим к
php-перечислениям:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\EnumType;

#[EnumType]
enum DirectionEnum: string
{
    case asc = 'asc';

    case desc = 'desc';
}
```

Атрибут `#[EnumType]` может содержать следующие параметры конструктора:

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
            Имя перечислимого типа. Если не указано, используется краткое имя php-перечисления.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание перечислимого типа, отображаемое в GraphQL схеме.
            Если не указано, используется описание php-перечисления, указанное в docBlock.
        </td>
    </tr>
</table>

> :point_right: **Обратите внимание!**
>
> Допустимыми значениями перечислимого GraphQL типа являются **все** (без исключения) значения
> соответствующего php-перечисления.

Для допустимых значений перечисления может быть задан атрибут `Andi\GraphQL\Attribute\EnumValue`.

Атрибут `#[EnumValue]` может содержать следующие параметры конструктора:

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
            Имя допустимого значения. Если не указано, используется краткое имя php-значения.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание допустимого значения, отображаемое в GraphQL схеме.
            Если не указано, используется описание php-значения, указанное в docBlock.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Если параметр задан, то в GraphQL схеме данное значение будет помечено устаревшим. В качестве
            причины будет указано значение данного параметра. Если параметр не задан, но в docBlock
            php-значения есть тэг <code>@deprecated</code>, то будет использован комментарий этого тега.
        </td>
    </tr>
</table>

## <a id="enum-type-via-interface">Определение путем реализации интерфейса</a>

> :point_right: **Рекомендация!**
>
> Воспользуйтесь абстрактным классом [`Andi\GraphQL\Type\AbstractEnumType`](abstract-enum-type.md).
> В нём уже реализованы требуемые методы.
>
> Библиотека позволяет определять GraphQL типы удобным для вас способом.
> При этом, созданные структуры могут ссылаться друг на друга.

Пример реализации интерфейса `EnumTypeInterface`:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\EnumValueInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;

final class AnimalEnum implements EnumTypeInterface
{
    public const DOG = 12;
    public const CAT = 15;

    public function getName(): string
    {
        return 'Animal';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getValues(): iterable
    {
        yield new class implements EnumValueInterface {
            ...
        };

        yield new class implements EnumValueInterface {
            ...
        };
    }
}
```

Интерфейс `EnumTypeInterface` требует реализации следующих методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен вернуть имя перечислимого типа.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание перечислимого типа, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getValues</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Метод должен возвращать итерируемую структуру (<code>array</code> или
                <code>\Traversable</code>) (пустая структура недопустима) - список допустимых значений
                перечисления.
            </p>
            <p>
                Каждый элемент структуры должен быть экземпляром класса, реализующего интерфейс
                <code>EnumValueInterface</code>
            </p>
        </td>
    </tr>
</table>

Допустимые значения перечислимого GraphQL типа должны быть реализованы с помощью интерфейса<br />
` Andi\GraphQL\Definition\Field\EnumValueInterface`.

> :point_right: **Рекомендация!**
>
> Для определения допустимого значения перечислимого типа воспользуйтесь классом
> [`Andi\GraphQL\Field\EnumValue`](abstract-enum-type.md#enum-value). В нём уже реализованы
> требуемые методы.

Пример реализации интерфейса `EnumValueInterface` (см. реализацию метода `getValues`):

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\EnumValueInterface;
use Andi\GraphQL\Definition\Type\EnumTypeInterface;
use Andi\GraphQL\Field\EnumValue;

final class AnimalEnum implements EnumTypeInterface
{
    public const DOG = 12;
    public const CAT = 15;

    ...

    public function getValues(): iterable
    {
        yield new class implements EnumValueInterface {
            public function getName(): string
            {
                return 'dog';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function getDeprecationReason(): ?string
            {
                return null;
            }

            public function getValue(): mixed
            {
                // Any php-value
                return AnimalEnum::DOG;
            }
        };

        yield new EnumValue(name: 'cat', value: AnimalEnum::CAT);
    }
}
```

Интерфейс `EnumValueInterface` требует реализации следующих методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен вернуть имя значения для отображения в GraphQL схеме.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание значение, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getDeprecationReason</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен возвращать описание причины, для отображения в GraphQL схеме, по которой
            значение перечислимого типа использовать не рекомендуется и <code>null</code>,
            если такая причина отсутствует.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            php-значение ассоциированное со значением перечислимого GraphQL типа.
            Может быть любым типом данных.
        </td>
    </tr>
</table>

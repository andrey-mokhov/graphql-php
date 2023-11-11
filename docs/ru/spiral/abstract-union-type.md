# Абстрактный класс AbstractUnionType

Абстрактный класс `Andi\GraphQL\Type\AbstractUnionType` позволяет определять объединенные GraphQL типы
без необходимости реализации методов. Основные методы уже реализовано в абстрактном классе, вам достаточно
задать значения его свойств, чтобы определить результат реализованных методов.

Пример реализации абстрактного класса:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Type\AbstractUnionType;
use GraphQL\Type\Definition as Webonyx;

final class ExampleAbstractUnionType extends AbstractUnionType implements ResolveTypeAwareInterface
{
    protected string $name = 'ExampleAbstractUnionType';

    protected iterable $types = [
        User::class,
        'pet',
    ];

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        if ($value instanceof User) {
            return 'User';
        }

        if (is_string($value)) {
            return Pet::class;
        }

        return null;
    }
}
```

При реализации объединяющего GraphQL типа с помощью абстрактного класса `AbstractUnionType` необходимо
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
        <td valign="top">Имя объединяющего типа, <b>обязательно</b> должно быть определено.</td>
    </tr>
    <tr>
        <td valign="top"><code>$description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание объединяющего типа, отображаемое в GraphQL схеме.
            Не определяйте значение, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>$types</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Итерируемая структура (<code>array</code> или <code>\Traversable</code>)
                (пустая структура недопустима) содержащая список имен объектных типов,
                составляющих объединенный тип.
            </p>
            <p>
                Допустимыми элементами итерируемой структуры являются краткие имена
                <a href="object-type.md">объектных GraphQL типов</a> или имена php классов,
                реализующих соответствующий объектный GraphQL тип.
            </p>
        </td>
    </tr>
</table>

Класс, определяющий объединенный GraphQL тип, может реализовать интерфейс `ResolveTypeAwareInterface`
(см. пример выше).

Интерфейс `ResolveTypeAwareInterface` требует реализации следующего метода:

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

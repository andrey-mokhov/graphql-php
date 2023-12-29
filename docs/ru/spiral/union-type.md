# Определение UnionType

Определение объединенных типов возможно путем реализации интерфейса
`Andi\GraphQL\Definition\Type\UnionTypeInterface`.

> :point_right: **Рекомендация!**
>
> Воспользуйтесь абстрактным классом [`Andi\GraphQL\Type\AbstractUnionType`](abstract-union-type.md).
> В нём уже реализованы требуемые методы.
>
> Библиотека позволяет определять GraphQL типы удобным для вас способом.
> При этом, созданные структуры могут ссылаться друг на друга.

Пример реализации интерфейса `UnionTypeInterface`:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ResolveTypeAwareInterface;
use Andi\GraphQL\Definition\Type\UnionTypeInterface;
use GraphQL\Type\Definition as Webonyx;

final class UserPetUnion implements UnionTypeInterface, ResolveTypeAwareInterface
{
    public function getName(): string
    {
        return 'UserPetUnion';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getTypes(): iterable
    {
        yield 'User';
        yield Pet::class;
    }

    public static function resolveType(mixed $value, mixed $context, Webonyx\ResolveInfo $info): ?string
    {
        if ($value instanceof User) {
            return User::class;
        }

        if (is_string($value)) {
            return 'pet';
        }

        return null;
    }
}
```

Интерфейс `UnionTypeInterface` требует реализации следующих методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен вернуть имя объединенного типа.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание объединенного типа, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getTypes</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Метод должен возвращать итерируемую структуру (<code>array</code> или
                <code>\Traversable</code>) (пустая структура недопустима) - список имен объектных типов,
                составляющих объединенный тип.
            </p>
            <p>
                Допустимыми значениями могут быть краткие имена
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
            объектного GraphQL типа или имя php-класса, реализующего соответствующий объектный тип.
        </td>
    </tr>
</table>

> :point_right: **Обратите внимание!**
>
> При использовании атрибутов для определения полей (Query, Mutation, ObjectType, InterfaceType),
> там где явно не указан тип данных, библиотека старается самостоятельно определить тип поля.
>
> В случае если указана дизъюнкция классов (каждый из которых определяет объектных GraphQL тип),
> библиотека соберет краткие имена классов, отсортирует их, сконкатенирует имена, добавив
> постфикс UnionType, и полученное имя постарается найти в реестре GraphQL типов. Если объединяющий
> GraphQL тип с таким именем не будет найден, библиотека зарегистрирует его.
>
> ```php
> #[ObjectField]
> public function getInitiator(): User|Admin|System
> {
>     ...
> }
> ```
>
> В примере выше будет создан и зарегистрирован объединяющий GraphQL тип с именем: `AdminSystemUserUnionType`.

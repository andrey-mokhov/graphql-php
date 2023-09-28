# Расширение типов

Механизм расширения объектных типов предусмотрен библиотекой изначально. Например определение
Query и Mutation полей с помощью атрибутов [`#[QueryField]`](query-filed.md№query-field-via-attribute) и
[`#[MutationField]`](mutation-field.md№mutation-field-via-attribute), это не что иное как расширение
соответствующих объектных типов `Query` и `Mutation`. Таким образом атрибут
`#[AdditionalField(targetType: 'Query')]` в точности повторяет логику атрибута<br />
`#[QueryField]`.

Таким образом, всё сказанное для [`#[QueryField]`](query-filed.md#query-field-via-attribute) справедливо
для `#[AdditionalField]`.

Пример использования атрибута `Andi\GraphQL\Attribute\AdditionalField`:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\Common\ResolverArguments;
use App\GraphQL\Type\User;
use App\GraphQL\Type\UserInterface;

final class UpperCaseName
{
    #[AdditionalField(targetType: User::class)]
    #[AdditionalField(targetType: UserInterface::class)]
    public function upperCaseName(ResolverArguments $arguments): string
    {
        /** @var User $user */
        $user = $arguments->object;

        return strtoupper($user->getDisplayName());
    }
}
```

Атрибут `#[AdditionalField]` применим к методам класса, имеет следующие параметры конструктора:

<table>
    <tr>
        <th>Имя</th>
        <th>Тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>targetType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                <b>Обязательный параметр</b>. Имя расширяемого объектного или интерфейсного GraphQL типа.
            </p>
            <p>
                Допустимыми значеними могут быть краткие имена объектных или интенрфейсных GraphQL типов
                или имена php классов, реализующих соответствующий GraphQL тип.
            </p>
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
                Тип поля. Допустимыми значеними могут быть краткие имена GraphQL типов
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
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Если параметр конструктора задан, то в GraphQL схеме поле будет помечено устаревшим.
            В качестве причины будет указано значение данного параметра. Если параметр не задан, но
            в docBlock метода есть тэг <code>@deprecated</code>, то будет использован комментарий
            этого тэга.
        </td>
    </tr>
</table>

Аргументами дополнительного поля являются параметры метода, помеченные php-атрибутом
`Andi\GraphQL\Attribute\Argument`. Подробнее об определение аргументов объектных полей изложено в
[Определение аргумента с помощью атрибута](argument.md#argument-via-attribute).

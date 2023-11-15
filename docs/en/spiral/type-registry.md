# Реестр GraphQL типов

В библиотеке используется единый реест GraphQL типов: `Andi\GraphQL\TypeRegistry`.

Реестр реализует интерфейс `Andi\GraphQL\TypeRegistryInterface`:

```php
namespace Andi\GraphQL;

use GraphQL\Type\Definition as Webonyx;

interface TypeRegistryInterface
{
    public function has(string $type): bool;

    public function get(string $type): Webonyx\Type;

    public function register(Webonyx\Type $type, string ...$aliases): void;

    public function getTypes(): iterable;
}
```

Назначение методов интерфейса:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание метода</th>
    </tr>
    <tr>
        <td valign="top"><code>has</code></td>
        <td valign="top"><code>bool</code></td>
        <td valign="top">
            <p>
                Возвращает <code>true</code> если запрошенное имя GraphQL типа зарегистрировано в реестре,
                и <code>false</code> в обратном случае.
            </p>
            <p>
                Псевдонимы GraphQL типов тоже учитываются при поиске в реестре.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>get</code></td>
        <td valign="top"><code>Webonyx\Type</code></td>
        <td valign="top">
            Для запрошенного имени возвращает определение GraphQL типа. Если имя GraphQL типа отсутствует
            в реестре - будет выброшено исключение.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>register</code></td>
        <td valign="top"><code>void</code></td>
        <td valign="top">
            <p>
                Регистрация GraphQL типа в реестре. Вторым и последующими параметрами можно перечислить
                псевдонимы регистрируемого типа, например: имя класса, определяющий GraphQL тип.
            </p>
            <p>
                В качестве примера: при регистрация скалярного типа <code>DateTime</code> целесообразно
                перечислить следующие псевдонимы: <code>\DateTimeInterface::class</code>,
                <code>\DateTimeImmutable::class</code>.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getTypes</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            Служебный метод. Возвращает зарегистрированные в реестре объектные типы, реализующие какой-либо
            интерфейс. Требуется для отображения в GraphQL схеме объектных типов (см. опцию конфигурации
            <code>types</code> <a href="https://webonyx.github.io/graphql-php/schema-definition/#configuration-options">Configuration Options</a>).
        </td>
    </tr>
</table>

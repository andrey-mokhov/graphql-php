# Определение ScalarType

Определение скалярных типов возможно путем реализации интерфейса
`Andi\GraphQL\Definition\Type\ScalarTypeInterface`.

> :point_right: **Рекомендация!**
>
> Воспользуйтесь абстрактным классом [`Andi\GraphQL\Type\AbstractScalarType`](abstract-scalar-type.md).
> В нём уже реализованы требуемые методы.
>
> Библиотека позволяет определять GraphQL типы удобным для вас способом.
> При этом, созданные структуры могут ссылаться друг на друга.

Пример реализации интерфейса `ScalarTypeInterface`:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ScalarTypeInterface;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;

final class Money implements ScalarTypeInterface
{
    public function getName(): string
    {
        return 'Money';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function serialize(mixed $value): int
    {
        return is_int($value)
            ? $value
            : throw new SerializationError("Int cannot represent non-integer value");
    }

    public function parseValue(mixed $value): int
    {
        return is_int($value)
            ? $value
            : throw new Error("Int cannot represent non-integer value");
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null): int
    {
        if ($valueNode instanceof IntValueNode) {
            return (int) $valueNode->value;
        }

        throw new Error("Int cannot represent non-integer value", $valueNode);
    }
}
```

Интерфейс `ScalarTypeInterface` требует реализацию следующих методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен вернуть имя скалярного типа.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание скалярного типа, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>serialize</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Метод должен преобразовать полученную php-структуру <code>$value</code> в скалярное значение.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>parseValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Метод должен преобразовать переменные (variables) GraphQL запроса во внутреннюю php-структуру.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>parseLiteral</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Метод должен преобразовать данные из тела GraphQL запроса во внутреннюю php-структуру.
        </td>
    </tr>
</table>

[Определение скалярных типов](https://webonyx.github.io/graphql-php/type-definitions/scalars/)
идентично определению в опорной библиотеке `webonyx/graphql-php`.

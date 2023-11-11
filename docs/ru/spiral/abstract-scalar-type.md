# Абстрактный класс AbstractScalarType

Абстрактный класс `Andi\GraphQL\Type\AbstractScalarType` позволяет определять скалярные GraphQL типы
без необходимости реализации методов `getName` и `getDescription`. Эти методы уже реализовано в абстрактном
классе, вам достаточно задать значения соответствующих свойств, чтобы определить результат этих методов.

Определение типа `Money` ([пример из смежного документа](scalar-type.md)) может выглядеть следующим
образом:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Type\AbstractScalarType;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;

final class Money implements AbstractScalarType
{
    protected string $name = 'Money';

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

Абстрактный класс `AbstractScalarType` частично реализует методы, требуемые в интерфейсе
`ScalarTypeInterface`. Вам потребуется реализовать следующие методы:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
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

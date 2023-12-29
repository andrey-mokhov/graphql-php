# Определение Mutation полей

По соглашению сообщества, `Mutation` поля предназначены для внесения изменений в состояние системы.
GraphQL тип `Mutation` является объектным типом, следовательно, его можно определить тем же образом,
как и любой другой [ObjectType](object-type.md).

В библиотеке подготовлен `Mutation` тип, возможностей которого достаточно для определения полей.
По умолчанию для `Mutation` типа используется класс `Andi\GraphQL\Type\MutationType`. Изменить класс,
реализующий тип `Mutation` можно в [настройках библиотеки](configure.md).

По умолчанию `Mutation` отключен, его необходимо предварительно включить
в [настройках библиотеки](configure.md).

Определение `Mutation` полей возможно с помощью:
- php-атрибута `Andi\GraphQL\Attribute\MutationField`;
- путем реализации интерфейса `Andi\GraphQL\Field\MutationFieldInterface`.

> :point_right: **Внимание!!!** :point_left:
>
> Определение `Mutation` и всё, что с ними связано, полностью совпадает с определением `Query`.
>
> Разница заключается только в именах используемых атрибутов и интерфейсов: вместо
> `#[QueryField]` потребуется атрибут<br />`#[MutationField]`; вместо `QueryFieldInterface`
> потребуется реализация `MutationFieldInterface` интерфейса.
> В остальном (аргументы поля или вспомогательные интерфейсы) полностью идентичны.

## <a id="mutation-field-via-attribute">Определение Mutation полей с помощью атрибута</a>

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Attribute\QueryField;

final class SimpleService
{
    #[QueryField(name: 'echo')]
    #[MutationField(name: 'echo')]
    public function echoMessage(#[Argument] string $message): string
    {
        return 'echo: ' . $message;
    }
}
```

## Определение Mutation полей с помощью интерфейса

Пример реализации интерфейса `MutationFieldInterface`:
```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\MutationFieldInterface;
use Andi\GraphQL\Field\QueryFieldInterface;
use GraphQL\Type\Definition as Webonyx;

final class ConcatService implements QueryFieldInterface, MutationFieldInterface
{
    public function getName(): string
    {
        return 'concat';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getDeprecationReason(): ?string
    {
        return null;
    }

    public function getType(): string
    {
        return Webonyx\StringType::class;
    }

    public function getMode(): int
    {
        return TypeAwareInterface::IS_REQUIRED;
    }
}
```

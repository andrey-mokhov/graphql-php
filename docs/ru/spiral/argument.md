# Определение аргументов полей

Определить аргументы поля можно:
- с помощью атрибута `Andi\GraphQL\Attribute\Argument` ([ссылка](#argument-via-attribute));
- путем реализации интерфейса `Andi\GraphQL\Definition\Field\ArgumentInterface`
  ([ссылка](#argument-via-interface)).

## <a id="argument-via-attribute">Определение аргумента с помощью атрибута</a>

Атрибут `#[Argument]` применим к параметрам метода. Метод, в свою очередь, должен иметь один
из следующих атрибутов:
- [`#[QueryField]`](query-filed.md#query-field-via-attribute) - поле Query типа;
- [`#[MutationField]`](mutation-field.md#mutation-field-via-attribute) - поле Mutation типа;
- [`#[ObjectField]`](object-field.md#object-field-via-attribute) - поле объектного типа;
- [`#[InterfaceField]`](interface-field.md#interface-field-via-attribute) - поле интерфейсного типа;
- [`#[AdditionalField]`](additional-field.md) - определение дополнительного поля для объектных и интерфейсных полей.

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Attribute\QueryField;
use App\GraphQL\Type\User;
use App\GraphQL\Type\UserInterface;

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
Атрибут `#[Argument]` может иметь следующие параметры конструктора:

<table>
    <tr>
        <th>Имя</th>
        <th>Тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Имя аргумента поля. Если не указано, используется имя параметра.</td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание аргумента поля. Если не указано, используется описание параметра, указанное
            в docBlock метода.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>type</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Тип аргумента поля. Допустимыми значеними могут быть краткие имена GraphQL типов
                (<code>'String'</code>, <code>'Int'</code> и т.д.) или имена php классов,
                реализующих соответствующий GraphQL тип (<code>StringType::class</code>,
                <code>IntType::class</code> и другие).
            </p>
            <p>
                Типом аргумента могут быть:
                <a href="scalar-type.md"><code>ScalarType</code></a>,
                <a href="enum-type.md"><code>EnumType</code></a>,
                <a href="input-object-type.md"><code>InputObjectType</code></a>.
            </p>
            <p>
                Если параметр <code>type</code> не указан, библиотека постарается определить
                значение самостоятельно (опираясь на определение параметра). Для php типов
                <code>array</code>, <code>iterable</code>, <code>mixed</code> и др. следует
                указать значение данного параметра явно. Параметр метода со spread оператором
                (<code>...</code>) будет преобразован в список соответствующего GraphQL типа,
                например: <code>string ...$messages</code> станет <code>[String!]</code>
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Если параметр конструктора задан, то в GraphQL схеме аргумент будет помечен устаревшим.
            В качестве причины будет указано значение этого параметра.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>defaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">
            Значение аргумента по умолчанию. Допустимы скалярные и Enum php-значения,
            а также <code>null</code>. Если параметр не задан, библиотека постарается определить
            значение по умолчанию самостоятельно (опираясь на определение параметра).
        </td>
    </tr>
</table>

> :point_right: **Обратите внимание!**
>
> При обработке GraphQL запроса будет вызван соответствующий метод php-класса. Для параметров с
> атрибутом `#[Argument]` будут использованы значения из GraphQL запроса, для остальных параметров
> значения из DI-контейнера.
>
> Вы должны быть уверены, что DI-контейнер сможет определить значения параметров метода.
>
> ```php
> final class SimpleService
> {
>     #[QueryField(name: 'echo')]
>     public function echoMessage(#[Argument] string $message, LoggerInterface $logger): string
>     {
>         $logger->info('incoming message', ['message' => $message]);
>
>         return 'echo: ' . $message;
>     }
> }
> ```
>
> В данном примере параметр `$logger` не будет отображен в GraphQL схеме, но будет доступен в методе.

## <a id="argument-via-interface">Определение аргумента с помощью интерфейса</a>

Реализация интерфейса `Andi\GraphQL\Definition\Field\ArgumentInterface` может потребоваться
при реализации метода `getArguments`, требуемого в интерфейсе
[`ArgumentsAwareInterface`](object-field.md#arguments-aware-interface). Который позволяет задать
аргументы для таких полей как:
- `QueryFieldInterface`
- `MutationFieldInterface`
- `ObjectFieldInterface`


> :point_right: **Рекомендация!** :point_left:
>
> Для определения аргументов полей вместо реализации интерфейса `ArgumentInterface`
> используйте класс `Andi\GraphQL\Argument\Argument`, в нём уже реализованы вспомогательные
> интерфейсы, а требуемые значения можно задать в конструкторе.

Пример реализации интерфейса `ArgumentInterface` (см. метод `getArguments`):

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Definition\Field\ArgumentInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\AbstractObjectField;
use Andi\GraphQL\Field\QueryFieldInterface;
use GraphQL\Type\Definition as Webonyx;

final class SquaringService extends AbstractObjectField implements QueryFieldInterface, ResolveAwareInterface
{
    protected string $name = 'square';
    protected string $type = 'Int';
    protected int $typeMode = TypeAwareInterface::IS_REQUIRED;

    public function getArguments(): iterable
    {
        yield new class implements ArgumentInterface {
            public function getName(): string
            {
                return 'num';
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function getType(): string
            {
                return 'Int';
            }

            public function getTypeMode(): int
            {
                return TypeAwareInterface::IS_REQUIRED;
            }

            public function hasDefaultValue(): bool
            {
                return false;
            }
        };
    }

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        return $args['num'] * $args['num'];
    }
}
```

Интерфейс <a id="argument-interface">`ArgumentInterface`</a> требует реализации следующих методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен возращать имя аргумента, отображаемое в GraphQL схеме.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание аргумента, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Должен вернуть тип аргумента. Допустимыми значеними могут быть краткие имена GraphQL
                типов (<code>'String'</code>, <code>'Int'</code> и т.д.) или имена php классов,
                реализующих соответствующий GraphQL тип (<code>StringType::class</code>,
                <code>IntType::class</code> и другие).
            </p>
            <p>
                Типом аргумента могут быть:
                <a href="scalar-type.md"><code>ScalarType</code></a>,
                <a href="enum-type.md"><code>EnumType</code></a>,
                <a href="input-object-type.md"><code>InputObjectType</code></a>.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getTypeMode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Должен вернуть битовую маску для модификатора типа аргумента. Возможны следующие значения:
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Без модификаторов, т.е. допустимы например числовые или <code>null</code>
                    значения аргумента.<br />
                    Эквивалент: <code>Int</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED</code></dt>
                <dd>
                    Модификатор исключающий <code>null</code> значение, т.е. значением аргумента будет
                    число.<br />
                    Эквивалент: <code>Int!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение аргумента допустимо. Таким образом значениями аргумента могут быть:
                    <code>null</code> значение, пустой массив, массив с числовыми или
                    <code>null</code> значениями.<br />
                    Эквивалент: <code>[Int]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение аргумента допустимо, но исключено в значениях. Таким образом зачениями
                    аргумента могут быть: <code>null</code> значение или непустой список с числовыми
                    значениями.<br />
                    Эквивалент: <code>[Int!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Допустимо объединение модификаторов путем побитового ИЛИ.<br />
                    Модификатор определяющий список значений (массив), исключающий <code>null</code>
                    значение аргумента, но позволяющий пустой список или список содержащий
                    числовые или <code>null</code> значения.<br />
                    Эквивалент: <code>[Int]!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий непустой список числовых значений (массив чисел).<br />
                    Эквивалент: <code>[Int!]!</code>
                </dd>
            </dl>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>hasDefaultValue</code></td>
        <td valign="top"><code>bool</code></td>
        <td valign="top">
            Должен вернуть <code>true</code> если аргумент имеет значение по умолчанию. Для определения
            значения по умолчанию следует реализовать интерфейс <code>DefaultValueAwareInterface</code>
            (см. <a href="#default-value-aware-interface">ниже</a>).
        </td>
    </tr>
</table>

Вспомогательные интерфейсы при определении аргумента поля:

<dl>
    <dt><a href="#default-value-aware-interface">DefaultValueAwareInterface</a></dt>
    <dd>Позволяет определить значение аргумента, используемого по умолчанию.</dd>
    <dt><a href="#deprecation-reason-aware-interface">DeprecationReasonAwareInterface</a></dt>
    <dd>Позволяет в GraphQL схеме указать причину, по которой аргумент использовать не рекомендуется.</dd>
</dl>

### <a id="default-value-aware-interface">DefaultValueAwareInterface</a>

Чтобы указать для аргумента значение по умолчанию следует реализовать интерфейс
`DefaultValueAwareInterface`, который требует реализации следующего метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getDefaultValue</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">Должен возращать значение, используемое аргументом по умолчанию.</td>
    </tr>
</table>

### <a id="deprecation-reason-aware-interface">DeprecationReasonAwareInterface</a>

Если в GraphQL схеме необходимо указать причины, по которой аргумент не рекомендуется использовать,
необходимо реализовать интерфейс `DeprecationReasonAwareInterface`, который требует реализации
следующего метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getDeprecationReason</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен возвращать описание причины, для отображения в GraphQL схеме, по которой
            аргумент использовать не рекомендуется и <code>null</code> значение, если такая причина
            отсутствует.
        </td>
    </tr>
</table>

> :point_right: **Рекомендация!** :point_left:
>
> При использовании класса `Argument` пример выше выглядел бы следующим образом:
> ```php
> namespace App\GraphQL\Field;
>
> use Andi\GraphQL\Argument\Argument;
> use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
> use Andi\GraphQL\Definition\Field\TypeAwareInterface;
> use Andi\GraphQL\Field\AbstractObjectField;
> use Andi\GraphQL\Field\QueryFieldInterface;
> use GraphQL\Type\Definition as Webonyx;
>
> final class SquaringService extends AbstractObjectField implements QueryFieldInterface, ResolveAwareInterface
> {
>     protected string $name = 'square';
>     protected string $type = 'Int';
>     protected int $typeMode = TypeAwareInterface::IS_REQUIRED;
>
>     public function getArguments(): iterable
>     {
>         yield new Argument(name: 'num', type: 'Int', typeMode: TypeAwareInterface::IS_REQUIRED);
>     }
>
>     public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
>     {
>         return $args['num'] * $args['num'];
>     }
> }
> ```

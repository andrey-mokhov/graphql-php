# Определение полей объектного GraphQL типа

В библиотеке `webonyx/graphql-php` для определения полей объектных GraphQL типов используется класс
[`FieldDefinion`](https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options).

Определение поля объектного GraphQL типа возможно:
- с помошью атрибута `Andi\GraphQL\Attribute\ObjectField` ([ссылка](#object-field-via-attribute));
- путем реализации интерфейса `Andi\GraphQL\Definition\Field\ObjectFieldInterface` ([ссылка](#object-field-via-interface));
- с помощью атрибута `Andi\GraphQL\Attribute\AdditionalField` ([ссылка](additional-field.md)).

## <a id="object-field-via-attribute">Определение полей с помощью атрибутов</a>

Определить поле объектного типа с помощью атрибута `#[ObjectField]` возможно только для классов,<br />
помеченных атрибутом `#[ObjectType]`. Атрибут может быть применен к свойствам и методам.

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;

#[ObjectType]
class User
{
    public function __construct(
        private readonly string $lastname,
        private readonly string $firstname,
        #[ObjectField]
        private readonly string $middlename,
    ) {
    }

    #[ObjectField]
    public function getLastname(): string
    {
        return $this->lastname;
    }

    ...
}
```

Атрибут `#[ObjectField]` может иметь следующие параметры конструктора:

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
            Имя поля. Если не указано, используется имя свойства или метода
            без префикса <code>get</code>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание поля. Если не указано, используется описание свойства/метода,
            указанное в docBlock. Для свойств, объявленных в конструкторе, в качестве
            описания используется комментарий к соответствующему параметру из docBlock конструктора.
        </td>
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
                Если параметр <code>type</code> не указан, библиотека постарается определить значение
                самостоятельно (опираясь на определение свойства/метода). Для php типов
                <code>array</code>, <code>iterable</code>, <code>mixed</code> и др. следует указать
                значение параметра явно.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>mode</code></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Модификатора типа поля. Параметр конструктора анализируется библиотекой в том случае,
            если тип поля указан и не содержит модификаторов. Возможны следующие значения:
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Без модификаторов, т.е. допустимы например строковые или <code>null</code>
                    значения.<br />
                    Эквивалент: <code>String</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED</code></dt>
                <dd>
                    Модификатор исключающий <code>null</code> значение, т.е. значение поля будет
                    строковым.<br />
                    Эквивалент: <code>String!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение поля допустимо. Таким образом значением поля может быть:
                    <code>null</code> значение, пустой массив, массив со строковыми или
                    <code>null</code> значениями.<br />
                    Эквивалент: <code>[String]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение поля допустимо, но исключено в значениях. Таким образом зачением поля
                    может быть: <code>null</code> значение или непустой список со строковыми
                    значениями.<br />
                    Эквивалент: <code>[String!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Допустимо объединение модификаторов путем побитового ИЛИ.<br />
                    Модификатор определяющий список значений (массив), исключающий <code>null</code>
                    значение поля, но позволяющий пустой список или список содержащий строковые или
                    <code>null</code> значения.<br />
                    Эквивалент: <code>[String]!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий непустой список строковых значений (массив строк).<br />
                    Эквивалент: <code>[String!]!</code>
                </dd>
            </dl>
        </td>
    </tr>
    <tr>
        <td valign="top"><code>deprecationReason</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Если параметр задан, то в GraphQL схеме данное поле будет помечено устаревшим. В качестве
            причины будет указано значение данного параметра. Если параметр не задан, но в
            docBlock (свойства/метода) есть тэг <code>@deprecated</code>, то будет использован
            комментарий этого тэга.
        </td>
    </tr>
</table>

Аргументы поля могут быть указаны для параметров метода с атрибутом `#[ObjectField]`, об этом подробно
изложено в [Определение аргумента поля](argument.md#argument-via-attribute).

> :point_right: **Обратите внимание!**
>
> Библиотека игнорируют область видимости свойств/методов, помеченные атрибутом свойства/методы будут
> вызваны для определения значения полей.


> :point_right: **Важно!** :point_left:
>
> Для определения значения полей, помеченных атрибутом `#[ObjectField]`, должен быть
> предоставлен экземпляр класса.
>
> Например:
> ```php
> class UserService
> {
>     #[QueryField(type: 'User!')]
>     public function getProfile(): array
>     {
>         // Raise exception:
>         // ReflectionMethod::__construct():
>         //     Argument #1 ($objectOrMethod) must be of type object|string, array given
>         return ['firstname' => 'foo', 'lastname' => 'bar'];
>     }
>
>     #[QueryField]
>     public function getCurrentUser(): User
>     {
>         // correct
>         return new User('Armstrong', 'Neil', 'Alden');
>     }
> }
> ```
>
> При этом, для полей, объявленных иным способом, таких ограничений нет.

## <a id="object-field-via-interface">Определение поля путем реализации интерфейса</a>

Определение полей с помощью интерфейса `ObjectFieldInterface` потребуется при реализации
интерфейса `FieldsAwareInterface`, об этом было написано
[здесь](object-type.md#object-type-interface-get-fields) и
[здесь](object-type.md#fields-aware-interface-get-fields).

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\FieldsAwareInterface;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;
use App\GraphQL\Field\UserFullName;

#[ObjectType]
class User implements UserInterface, InterfacesAwareInterface, FieldsAwareInterface
{
    ...

    public function getFields(): iterable
    {
        yield new UserFullName();
    }
}
```

> :point_right: **Рекомендация!**
>
> Воспользуйтесь абстрактным классом `Andi\GraphQL\Field\AbstractObjectField`, в нём уже реализованы
> основные методы требуемые в интерфейсе.

Пример реализации интерфейса `ObjectFieldInterface`:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;

final class UserFullName implements ObjectFieldInterface
{
    public function getName(): string
    {
        return 'fullName';
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
        return 'String';
    }

    public function getMode(): int
    {
        return TypeAwareInterface::IS_REQUIRED;
    }
}
```

Интерфейс `ObjectFieldInterface` требует реализации следующих методов:

<table>
    <tr>
        <th>Имя метода</th>
        <th>Возвращаемый тип</th>
        <th>Описание метода</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен возращать имя поля.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание поля, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getDeprecationReason</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен возвращать описание причины, для отображения в GraphQL схеме, по которой
            поле использовать не рекомендуется и <code>null</code> значение, если такая причина
            отсутствует.
        </td>
    </tr>
    <tr>
        <td valign="top"><code>getType</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            <p>
                Должен вернуть тип данных поля. Допустимыми значеними могут быть краткие имена
                GraphQL типов (<code>'String'</code>, <code>'Int'</code> и т.д.) или имена php классов,
                реализующих соответствующий GraphQL тип (<code>StringType::class</code>,
                <code>IntType::class</code> и другие).</p>
            <p>
                Типом поля могут быть:
                <a href="scalar-type.md">ScalarType</a>, <a href="enum-type.md">EnumType</a>,
                <a href="object-type.md">ObjectType</a>, <a href="interface-type.md">InterfaceType</a>,
                <a href="union-type.md">UnionType</a>.
            </p>
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="get-type-mode"><code>getMode</code></a></td>
        <td valign="top"><code>int</code></td>
        <td valign="top">
            Должен вернуть битовую маску для модификатора типа поля. Возможны следующие значения:
            <dl>
                <dt><code>TypeAwareInterface::NONE</code></dt>
                <dd>
                    Без модификаторов, т.е. допустимы например строковые или <code>null</code>
                    значения.<br />
                    Эквивалент: <code>String</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED</code></dt>
                <dd>
                    Модификатор исключающий <code>null</code> значение, т.е. значение поля будет
                    строковым.<br />
                    Эквивалент: <code>String!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение поля допустимо. Таким образом значением поля может быть:
                    <code>null</code> значение, пустой массив, массив со строковыми или
                    <code>null</code> значениями.<br />
                    Эквивалент: <code>[String]</code>
                </dd>
                <dt><code>TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий список значений (массив), при этом <code>null</code>
                    значение поля допустимо, но исключено в значениях. Таким образом зачением поля
                    может быть: <code>null</code> значение или непустой список со строковыми
                    значениями.<br />
                    Эквивалент: <code>[String!]</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::IS_LIST</code></dt>
                <dd>
                    Допустимо объединение модификаторов путем побитового ИЛИ.<br />
                    Модификатор определяющий список значений (массив), исключающий <code>null</code>
                    значение поля, но позволяющий пустой список или список содержащий строковые или
                    <code>null</code> значения.<br />
                    Эквивалент: <code>[String]!</code>
                </dd>
                <dt><code>TypeAwareInterface::IS_REQUIRED | TypeAwareInterface::ITEM_IS_REQUIRED</code></dt>
                <dd>
                    Модификатор определяющий непустой список строковых значений (массив строк).<br />
                    Эквивалент: <code>[String!]!</code>
                </dd>
            </dl>
        </td>
    </tr>
</table>

Для расширения возможностей поля потребуются реализация дополнительных интерфейсов:

<dl>
    <dt><a href="#arguments-aware-interface"><code>ArgumentsAwareInterface</code></a></dt>
    <dd>
        Позволяет определить аргументы поля.
        В абстрактном классе <code>AbstractObjectField</code> данный интерфейс уже реализован.
    </dd>
    <dt><a href="#resolve-aware-interface"><code>ResolveAwareInterface</code></a></dt>
    <dd>Потребует реализацию метода, вычисляющую значение поля.</dd>
    <dt><a href="#complexity-aware-interface"><code>ComplexityAwareInterface</code></a></dt>
    <dd>
        Позволяет определить метод <code>complexity</code>, используемый для ограничения сложности
        запроса. Подробнее в разделе
        <a href="https://webonyx.github.io/graphql-php/security/#query-complexity-analysis">Security</a>.
    </dd>
</dl>

### <a id="arguments-aware-interface">ArgumentsAwareInterface</a>

Пример реализации интерфейса `ArgumentsAwareInterface`:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;

final class UserFullName implements ObjectFieldInterface, ArgumentsAwareInterface
{
    ...

    public function getArguments(): iterable
    {
        yield new Argument(
            name: 'separator',
            type: 'String',
            mode: TypeAwareInterface::IS_REQUIRED,
            defaultValue: ' ',
        );
    }
}
```

Интерфейс `ArgumentsAwareInterface` требует реализации следующего метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getArguments</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>Метод должен возвращать итерируемую структуру (<code>array</code> или <code>\Traversable</code>)
            (допустима пустая структура - в этом случае поле будет без аргументов).</p>
            <p>Возвращаемая структура может содержать элементы следующих типов:</p>
            <dl>
                <dt>Массив конфигурации аргумента</dt>
                <dd>
                    Массив должен соответствовать требованиям к конфигурации аргументов
                    <code>webonyx/graphql-php</code> библиотеки. Подробнее см.
                    <a href="https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-argument-configuration-options">официальную документацию</a>.
                </dd>
                <dt>Объект, реализующий интерфейс <code>ArgumentInterface</code></dt>
                <dd>
                    В примере выше, класс <code>Argument</code> реализует требуемый интерфейс,
                    требования которого изложены в разделе
                    <a href="argument.md#argument-via-interface">Определение аргумента с помощью интерфейса</a>.
                </dd>
            </dl>
        </td>
    </tr>
</table>


### <a id="resolve-aware-interface">ResolveAwareInterface</a>

Пример реализации интерфейса `ResolveAwareInterface`:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use App\GraphQL\Type\User;
use GraphQL\Type\Definition as Webonyx;

final class UserFullName implements ObjectFieldInterface, ArgumentsAwareInterface, ResolveAwareInterface
{
    ...

    public function resolve(mixed $objectValue, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        /** @var User $objectValue */
        return implode(
            $args['separator'],
            [
                $objectValue->getLastname(),
                $objectValue->getFirstname(),
                (new \ReflectionProperty($objectValue, 'middlename'))->getValue($objectValue),
            ],
        );
    }
}
```

Интерфейс требует реализации единственного метода `resolve`. Возвращаемое значение будет
результирующим значением поля.

Сигнатура метода `resolve` соответствует требованиям для опции `resolve` в
[конфигурации поля](https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options).

### <a id="complexity-aware-interface">ComplexityAwareInterface</a>

Пример реализации интерфейса `ComplexityAwareInterface`:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ComplexityAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use Andi\GraphQL\Definition\Field\ResolveAwareInterface;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use App\GraphQL\Type\User;
use GraphQL\Type\Definition as Webonyx;

final class UserFullName implements
    ObjectFieldInterface,
    ArgumentsAwareInterface,
    ResolveAwareInterface,
    ComplexityAwareInterface
{
    ...

    public function complexity(int $childrenComplexity, array $args): int
    {
        return $childrenComplexity + 1;
    }
}
```

Интерфейс требует реализации единственного метода `complexity`. Возвращаемое методом значение
определяет сложность вычисления поля.

Подробнее в разделе [Security](https://webonyx.github.io/graphql-php/security/#query-complexity-analysis).

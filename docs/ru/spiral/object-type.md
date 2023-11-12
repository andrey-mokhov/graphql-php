# Определение ObjectType

Определение объектных типов возможно:
- с помощью атрибута `Andi\GraphQL\Attribute\ObjectType` ([ссылка](#object-type-via-attribute));
- путем реализации интерфейса `Andi\GraphQL\Definition\Type\ObjectTypeInterface` ([ссылка](#object-type-via-interface)).

## <a id="object-type-via-attribute">Определение с помощью атрибута</a>

Для определения объектного типа используйте атрибут `#[ObjectType]`, данный атрибут применим к классам:
```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectType;

#[ObjectType]
class User
{
    ...
}
```

Атрибут `#[ObjectType]` может содержать следующие параметры конструктора:

<table>
    <tr>
        <th>Имя</th>
        <th>Тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>name</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Имя объектного типа. Если не указано, используется краткое имя класса.</td>
    </tr>
    <tr>
        <td valign="top"><code>description</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">
            Описание объектного типа, отображаемое в GraphQL схеме.
            Если не указано, используется описание класса, указанное в docBlock.
        </td>
    </tr>
</table>

В разделе [Определение полей объектного GraphQL типа](object-field.md) подробно изложены возможности
библиотеки.

Класс с атрибутом `#[ObjectType]` может, в том числе, реализовывать вспомогательные интерфейсы:
- [`FieldsAwareInterface`](#fields-aware-interface) для определения дополнительных полей;
- [`InterfacesAwareInterface`](#interfaces-aware-interface) для определения реализованных интерфейсов;
- [`ResolveFieldAwareInterface`](#resolve-field-aware-interface) для определения резолвера,
  используемого объектным типом по умолчанию;
- [`IsTypeOfAwareInterface`](#is-type-of-aware-interface) для идентификации объектного типа.

## <a id="object-type-via-interface">Определение путем реализации интерфейса</a>

> :point_right: **Рекомендация!**
>
> Воспользуйтесь абстрактным классом [`Andi\GraphQL\Type\AbstractObjectType`](abstract-object-type.md).
> В нём уже реализованы требуемые методы.
>
> Библиотека позволяет определять GraphQL типы удобным для вас способом.
> При этом, созданные структуры могут ссылаться друг на друга.

Пример реализации интерфейса `ObjectTypeInterface`:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

class Pet implements ObjectTypeInterface
{
    public function getName(): string
    {
        return 'pet';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new Webonyx\FieldDefinition([
            'name' => 'nickname',
            'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
            'resolve' => static fn (string $nickname) => $nickname,
        ]);
    }
}
```

Интерфейс `ObjectTypeInterface` требует реализации следующих методов:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getName</code></td>
        <td valign="top"><code>string</code></td>
        <td valign="top">Должен вернуть имя объектного типа.</td>
    </tr>
    <tr>
        <td valign="top"><code>getDescription</code></td>
        <td valign="top"><code>string | null</code></td>
        <td valign="top">
            Должен вернуть описание объектного типа, отображаемое в GraphQL схеме.
            Следует вернуть <code>null</code>, если описание не требуется.
        </td>
    </tr>
    <tr>
        <td valign="top"><a id="object-type-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Метод должен возвращать итерируемую структуру (<code>array</code> или
                <code>\Traversable</code>) (пустая структура недопустима) - список полей объектного
                типа.
            </p>
            <p>Каждый элемент структуры может быть:</p>
            <ul>
                <li>
                    экземпляром класса <code>FieldDefinition</code>
                    (см. <a href="https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options">Field configuration options</a>).
                </li>
                <li>
                    экземпляром класса, реализующего интерфейс <code>ObjectFieldInterface</code>
                    (см. <a href="object-field.md#object-field-via-interface">Определение полей объектного типа путем реализации интерфейса</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

### <a id="object-type-interfaces">Вспомогательные интерфейсы</a>

Для расширения возможностей объектных GraphQL типов, вам может потребоваться реализация интерфейсов,
перечисленных ниже.

<dl>
    <dt><a href="#fields-aware-interface">FieldsAwareInterface</a></dt>
    <dd>
        Позволяет определить список полей объектного типа. Интерфейс <code>ObjectTypeInterface</code>
        уже реализует данный интерфейс. Будет полезен при расширении полей объектного типа,
        объявленного с помощью атрибута <code>#[ObjectType]</code>.
    </dd>
    <dt><a href="#interfaces-aware-interface">InterfacesAwareInterface</a></dt>
    <dd>
        Позволяет определить список <a href="interface-type.md">интерфейсных типов</a> реализованных
        в объектном типе.
    </dd>
    <dt><a href="#resolve-field-aware-interface">ResolveFieldAwareInterface</a></dt>
    <dd>
        Позволяет указать метод, используемый в объектном типе по умолчанию для определения
        значений полей.
    </dd>
    <dt><a href="#is-type-of-aware-interface">IsTypeOfAwareInterface</a></dt>
    <dd>Позволяет установить, относятся ли анализируемые данные к объектному типу.</dd>
    <dt><a href="#dynamic-object-type-interface">DynamicObjectTypeInterface</a></dt>
    <dd>
        Позволяет расширять объектный тип дополнительными полями, опреденными вне класса.
    </dd>
</dl>

#### <a id="fields-aware-interface">FieldsAwareInterface</a>

Интерфейс `FieldsAwareInterface` требует реализацию единственного метода (о нём уже упоминалось
[выше](#object-type-interface-get-fields)):

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><a id="fields-aware-interface-get-fields"><code>getFields</code></a></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Метод должен возвращать итерируемую структуру (<code>array</code> или
                <code>\Traversable</code>) (пустая структура недопустима).
            </p>
            <p>Каждый элемент структуры может быть:</p>
            <ul>
                <li>
                    экземпляром класса <code>FieldDefinition</code>
                    (см. <a href="https://webonyx.github.io/graphql-php/type-definitions/object-types/#field-configuration-options">Field configuration options</a>).
                </li>
                <li>
                    экземпляром класса, реализующего интерфейс <code>ObjectFieldInterface</code>
                    (см. <a href="object-field.md#object-field-via-interface">Определение полей объектного типа путем реализации интерфейса</a>).
                </li>
            </ul>
        </td>
    </tr>
</table>

#### <a id="interfaces-aware-interface">InterfacesAwareInterface</a>

Интерфейс `Andi\GraphQL\Definition\Type\InterfacesAwareInterface` позволяет определить перечень
интерфейсных GraphQL типов, которые реализует ваш объектный GraphQL тип.

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;

#[ObjectType]
class User implements UserInterface, InterfacesAwareInterface
{
    ...

    public function getInterfaces(): iterable
    {
        yield UserInterface::class;
    }
}
```

Интерфейс `InterfacesAwareInterface` требует реализацию единственного метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>getInterfaces</code></td>
        <td valign="top"><code>iterable</code></td>
        <td valign="top">
            <p>
                Метод должен возвращать итерируемую структуру (<code>array</code> или
                <code>\Traversable</code>) (допустима пустая структура).
            </p>
            <p>
                Каждый элемент структуры может быть:
            </p>
            <ul>
                <li>
                    кратким именем интерфейсного GraphQL типа
                    (например: <code>'UserInterface'</code>)
                </li>
                <li>
                    именем php класса, реализующего соответствующий интерфейсный GraphQL тип
                    (например: <code>UserInterface::class</code>).
                </li>
            </ul>
        </td>
    </tr>
</table>

#### <a id="resolve-field-aware-interface">ResolveFieldAwareInterface</a>

Интерфейс `Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface` требует реализации метода,
используемого для вычисления значений полей объектного типа (если у поля не задан собственный резолвер):

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\ResolveFieldAwareInterface;

#[ObjectType]
class User implements UserInterface, ResolveFieldAwareInterface
{
    private array $attributes = [];

    ...

    public function resolveField(mixed $value, array $args, mixed $context, Webonyx\ResolveInfo $info): mixed
    {
        $field = $info->fieldName;

        return $this->attributes[$field] ?? null;
    }
}
```

Интерфейс `ResolveFieldAwareInterface` требует реализации единственного метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>resolveField</code></td>
        <td valign="top"><code>mixed</code></td>
        <td valign="top">Должен вернуть значение поля, указанного в параметре <code>$info</code>.</td>
    </tr>
</table>

#### <a id="is-type-of-aware-interface">IsTypeOfAwareInterface</a>

Интерфейс `Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface` требует определить метод,
который должен вернуть `true`, если значение относится к объектному GraphQL типу.

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\IsTypeOfAwareInterface;

#[ObjectType]
class User implements UserInterface, IsTypeOfAwareInterface
{
    ...

    public function isTypeOf(mixed $value, mixed $context, Webonyx\ResolveInfo $info): bool
    {
        return is_object($value) && $value::class === self::class;
    }
}
```

Интерфейс `IsTypeOfAwareInterface` требует реализации единственного метода:

<table>
    <tr>
        <th>Имя</th>
        <th>Возвращаемый тип</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td valign="top"><code>isTypeOf</code></td>
        <td valign="top"><code>bool</code></td>
        <td valign="top">
            Должен вернуть <code>true</code>, если первый параметр метода
            ассоциирован с объектным типом.
        </td>
    </tr>
</table>

Данный метод [будет использован](https://webonyx.github.io/graphql-php/type-definitions/interfaces/#interface-role-in-data-fetching)
при идентификации объектного GraphQL типа, если интерфейсный тип не содержит собственного резолвера
(на практике не удалось заставить работать заявленную в `webonyx/graphql-php` логику).

#### <a id="dynamic-object-type-interface">DynamicObjectTypeInterface</a>

Интерфейс `Andi\GraphQL\Type\DynamicObjectTypeInterface` добавляет возможность расширения
объектных GraphQL типов дополнительными полями, определенных в иных классах
(данная механика подробно описана в [Расширение типов](additional-field.md)).

Реализация данного интерфейса влияет на определение метода `getFields`, см. пример ниже:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Type\ObjectTypeInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use GraphQL\Type\Definition as Webonyx;

class Pet implements ObjectTypeInterface, DynamicObjectTypeInterface
{
    private array $additionalFields = [];

    public function getName(): string
    {
        return 'pet';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getFields(): iterable
    {
        yield new Webonyx\FieldDefinition([
            'name' => 'nickname',
            'type' => Webonyx\Type::nonNull(Webonyx\Type::string()),
            'resolve' => static fn (string $nickname) => $nickname,
        ]);

        yield from $this->additionalFields;
    }

    public function addAdditionalField(mixed $field): static
    {
        $this->additionalFields[] = $field;

        return $this;
    }
}
```

> :point_right: **Важно!**
>
> Объектные GraphQL типы объявленные с помощью атрибута `#[ObjectType]` уже являются расширяемыми.

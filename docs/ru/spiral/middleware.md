# Разработка middleware слоев

Процесс загрузки приложения и обработка пользовательского запроса представлена на схеме ниже:

![Загрузка приложения](../../images/graphql-process.png)

При запуске worker'а происходит инициализация приложения с последующим ожиданием пользовательского запроса,
подробнее об этапах инициализации приложения можно ознакомится в
[официальной документации SpiralFramework](https://spiral.dev/docs/framework-kernel/current/en).

Рассмотрим подробнее инициализацию GraphQL сервера:

1. Запуск worker'а
2. Инициализация `GraphQLBootloader`
   - регистрация `GraphQLMiddleware` в `HttpBootloader`. Следует убедиться, что `GraphQLMiddleware` будет
     зарегистрирован после `JsonPayloadMiddleware` (об этом уже [было написано ранее](install.md#bootloader));
   - регистрация `Query`, `Mutation` и дополнительных GraphQL типов (см. [опцию `additionalTypes`](configure.md))
     в реестре (`TypeRegistry`);
   - регистрация слушателей в `TokenizerListenerRegistryInterface` для статического анализа приложения с целью
     автоматической регистрации GraphQL типов, Query и Mutation полей, а также для расширения объектных и
     интерфейсных GraphQL типов. Более подробную информацию о статическом анализе приложения
     [смотрите в официальной документации SpiralFramework](https://spiral.dev/docs/advanced-tokenizer/current);
   - регистрация `SchemaWarmupper` для последующего запуска процесса самоанализа
     (об этом [чуть ниже](#introspection));
3. Вызов зарегистрированных `booted` замыканий
   - осуществляется статический анализ приложения;
   - анализируются классы, перечисления и интерфейсы. Если их определения подходят под критерии GraphQL типов -
     осуществляется преобразование определения к соответствующему GraphQL типу (подробнее
     [ниже](#type-resolver)). Анализ и регистрация GraphQL типов осуществляется в методе `listen` слушателей
     ([см. подробнее](https://spiral.dev/docs/advanced-tokenizer/current#crafting-a-listener));
   - анализируются классы. Если их определения подходят под критерии определения полей объектных типов -
     осуществляется расширение соответствующих типов (см. [QueryField](query-filed.md),
     [MutationField](mutation-field.md) и [AdditionalField](additional-field.md)).
     Анализ и расширение GraphQL типов осуществляется в методе `finalize` слушателей
     ([см. подробнее](https://spiral.dev/docs/advanced-tokenizer/current#crafting-a-listener)). Ввиду того,
     что регистрация в реестре GraphQL типов осуществляется в методе `listen`, на момент вызова `finalize` все
     GraphQL типы уже будут зарегистрированы;
4. Вызов зарегистрированных `bootstrapped` замыканий
   - выполняется <a id="introspection">самоанализ GraphQL схемы</a>. При этом:
     - для `ObjectType` и `InterfaceType` извлекаются поля. Каждое поле обрабатывается с помощью middleware
       конвейера `ObjectFieldResolver` (подробнее [ниже](#object-field-resolver)). Аргументы полей также
       извлекаются и обрабатываются middleware конвейером `ArgumentResolver` (подробнее
       [ниже](#argument-resolver));
     - для `EnumType` извлекаются возможные значения;
     - для `InputObjectType` извлекаются поля. Каждое поле обрабатывается с помощью middleware конвейера
       `InputObjectFieldResolver` (подробнее [ниже](#input-object-field-resolver));
     - для `UnionType` извлекаются типы, входящие в объединенный тип.
   - разогрев схемы завершен.
5. worker ожидает пользовательский запрос, после получения приступает к его обработке.

## Middleware конвейеры

Библиотека в своей работе использует middleware конвейеры. Данное решение позволяет расширять функциональность
библиотеки.

В работе библиотека использует следующие конвейеры:
- `TypeResolver` - предназначен для [определения GraphQL типа](#type-resolver);
- `ObjectFieldResolver` - предназначен для [определения поля](#object-field-resolver) объектного и
  интерфейсного GraphQL типа;
- `InputObjectFieldResolver` - предназначен для [определения поля](#input-object-field-resolver)
  входящего объектного GraphQL типа;
- `ArgumentResolver` - предназначен для [определения аргумента](#argument-resolver) поля.

### <a id="type-resolver">Middleware конвейер для определения GraphQL типа</a>

Конвейер `TypeResolver` реализует два основных метода:
- `pipe` - предназначен для регистрации middleware слоя в конвейере с указанным приоритетом исполнения.
  Чем приоритет больше - тем раньше будет вызван middleware слой. Слои с одинаковым приоритетом будут
  вызваны в порядке их регистрации в конвейере;
- `resolve` - осуществляет запуск конвейера. Метод возвращает GraphQL тип соответствующий входящему параметру.

```php
namespace Andi\GraphQL\TypeResolver;

use Andi\GraphQL\TypeResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\TypeResolver\Middleware\PipelineInterface;
use GraphQL\Type\Definition as Webonyx;

final class TypeResolver implements PipelineInterface
{
    public function resolve(mixed $type): Webonyx\Type
    {
        ...
    }

    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void
    {
        ...
    }
}
```

Регистрируемый в конвейере middleware слой должен реализовывать следующий интерфейс:

```php
namespace Andi\GraphQL\TypeResolver\Middleware;

use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;

interface MiddlewareInterface
{
    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type;
}
```

Если входящий параметр `$type` может быть ассоциирован с GraphQL типом, метод `process` должен вернуть
соответствующий GraphQL тип, в обратном случае передать управление последующему middleware слою.

Пример реализации middleware слоя:

```php
use GraphQL\Type\Definition as Webonyx;

final class ObjectTypeConfigurationMiddleware implements MiddlewareInterface
{
    public function process(mixed $type, TypeResolverInterface $typeResolver): Webonyx\Type
    {
        $isObjectType = is_array($type)
            && isset($type['name'], $type['fields'])
            && is_string($type['name'])
            && (is_iterable($type['fields']) || is_callable($type['fields']));

        return $isObjectType
            ? new Webonyx\ObjectType($type)
            : $typeResolver->resolve($type);
    }
}
```

В примере выше middleware слой анализирует входящий параметр `$type`, если он является массивом содержащий
опции `name` и `fields` логика принимает решение о том, что параметр может быть интерпретирован как
объектный GraphQL тип.

> :point_right: Обратите внимание!
>
> Приведенный выше пример реализации middleware слоя не следует использовать в приложении, т.к.
> конфигурация входящего объектного GraphQL типа содержит аналогичные опции конфигурации.

Для регистрации middleware слоя в конвейере, укажите его в опции `typeResolverMiddlewares`
[конфигурации](configure.md) библиотеки. Где ключем должно быть имя класса, а значением - приоритет
исполнения.

### <a id="object-field-resolver">Middleware конвейер для определения поля объектного или интерфейсного типа</a>

Конвейер `ObjectFieldResolver` реализует два основных метода:
- `pipe` - предназначен для регистрации middleware слоя в конвейере с указанным приоритетом исполнения.
  Чем приоритет больше - тем раньше будет вызван middleware слой. Слои с одинаковым приоритетом будут
  вызваны в порядке их регистрации в конвейере;
- `resolve` - осуществляет запуск конвейера. Метод возвращает определение поля соответствующее входящему
  параметру.

```php
namespace Andi\GraphQL\ObjectFieldResolver;

use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\PipelineInterface;
use GraphQL\Type\Definition as Webonyx;

final class ObjectFieldResolver implements PipelineInterface
{
    public function resolve(mixed $field): Webonyx\FieldDefinition
    {
        ...
    }

    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void
    {
        ...
    }
}
```

Регистрируемый в конвейере middleware слой должен реализовывать следующий интерфейс:

```php
namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;

interface MiddlewareInterface
{
    public function process(mixed $field, ObjectFieldResolverInterface $fieldResolver): Webonyx\FieldDefinition;
}
```

Если входящий параметр `$field` может быть ассоциирован с полем объектного или интерфейсного типа,
метод `process` должен вернуть определение поля, в обратном случае передать управление последующему
middleware слою.

Пример реализации middleware слоя:

```php
namespace Andi\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;

final class WebonyxObjectFieldMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function process(mixed $field, ObjectFieldResolverInterface $fieldResolver): Webonyx\FieldDefinition
    {
        return $field instanceof Webonyx\FieldDefinition
            ? $field
            : $fieldResolver->resolve($field);
    }
}
```

Для регистрации middleware слоя в конвейере, укажите его в опции `objectFieldResolverMiddlewares`
[конфигурации](configure.md) библиотеки. Где ключем должно быть имя класса, а значением - приоритет
исполнения.

### <a id="input-object-field-resolver">Middleware конвейер для определения поля входящего объектного GraphQL типа</a>

Конвейер `InputObjectFieldResolver` реализует два основных метода:
- `pipe` - предназначен для регистрации middleware слоя в конвейере с указанным приоритетом исполнения.
  Чем приоритет больше - тем раньше будет вызван middleware слой. Слои с одинаковым приоритетом будут
  вызваны в порядке их регистрации в конвейере;
- `resolve` - осуществляет запуск конвейера. Метод возвращает определение поля соответствующее входящему
  параметру.

```php
namespace Andi\GraphQL\InputObjectFieldResolver;

use Andi\GraphQL\InputObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\PipelineInterface;
use GraphQL\Type\Definition as Webonyx;

final class InputObjectFieldResolver implements PipelineInterface
{
    public function resolve(mixed $field): Webonyx\InputObjectField
    {
        ...
    }

    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void
    {
        ...
    }
}
```

Регистрируемый в конвейере middleware слой должен реализовывать следующий интерфейс:

```php
namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;

interface MiddlewareInterface
{
    public function process(mixed $field, InputObjectFieldResolverInterface $fieldResolver): Webonyx\InputObjectField;
}
```

Если входящий параметр `$field` может быть ассоциирован с полем входящего объектного типа,
метод `process` должен вернуть определение поля, в обратном случае передать управление последующему
middleware слою.

Пример реализации middleware слоя:

```php
namespace Andi\GraphQL\InputObjectFieldResolver\Middleware;

use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use GraphQL\Type\Definition as Webonyx;

final class WebonyxInputObjectFieldMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function process(mixed $field, InputObjectFieldResolverInterface $fieldResolver): Webonyx\InputObjectField
    {
        return $field instanceof Webonyx\InputObjectField
            ? $field
            : $fieldResolver->resolve($field);
    }
}
```

Для регистрации middleware слоя в конвейере, укажите его в опции `inputObjectFieldResolverMiddlewares`
[конфигурации](configure.md) библиотеки. Где ключем должно быть имя класса, а значением - приоритет
исполнения.

### <a id="argument-resolver">Middleware конвейер для определения аргумента поля</a>

Конвейер `ArgumentResolver` реализует два основных метода:
- `pipe` - предназначен для регистрации middleware слоя в конвейере с указанным приоритетом исполнения.
  Чем приоритет больше - тем раньше будет вызван middleware слой. Слои с одинаковым приоритетом будут
  вызваны в порядке их регистрации в конвейере;
- `resolve` - осуществляет запуск конвейера. Метод возвращает конфигурацию аргумента соответствующее
  входящему параметру.

```php
namespace Andi\GraphQL\ArgumentResolver;

use Andi\GraphQL\ArgumentResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ArgumentResolver\Middleware\PipelineInterface;

final class ArgumentResolver implements PipelineInterface
{
    public function resolve(mixed $argument): array
    {
        ...
    }

    public function pipe(MiddlewareInterface $middleware, int $priority = 0): void
    {
        ...
    }
}
```

Регистрируемый в конвейере middleware слой должен реализовывать следующий интерфейс:

```php
namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;

interface MiddlewareInterface
{
    public function process(mixed $argument, ArgumentResolverInterface $argumentResolver): array;
}
```

Если входящий параметр `$argument` может быть ассоциирован с аргументом поля, метод `process` должен
вернуть конфигурацию аргумента, в обратном случае передать управление последующему middleware слою.

Пример реализации middleware слоя:

```php
namespace Andi\GraphQL\ArgumentResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use GraphQL\Type\Definition as Webonyx;

final class ArgumentConfigurationMiddleware implements MiddlewareInterface
{
    public const PRIORITY = 1024;

    public function process(mixed $argument, ArgumentResolverInterface $argumentResolver): array
    {
        $isConfig = is_array($argument)
            && isset($argument['name'], $argument['type'])
            && is_string($argument['name'])
            && $argument['type'] instanceof Webonyx\Type;

        return $isConfig
            ? $argument
            : $argumentResolver->resolve($argument);
    }
}
```

Для регистрации middleware слоя в конвейере, укажите его в опции `argumentResolverMiddlewares`
[конфигурации](configure.md) библиотеки. Где ключем должно быть имя класса, а значением - приоритет
исполнения.

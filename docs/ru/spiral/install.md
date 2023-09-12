# GraphQL библиотека для SpiralFramework

## Установка

 Установка [composer](https://getcomposer.org/doc/00-intro.md) пакета:

```shell
composer require andi-lab/graphql-php-spiral
```

## Настройка загрузчика

 Добавьте `Andi\GraphQL\Spiral\Bootloader\GraphQLBootloader` в список загрузчиков `App\Application\Kernel::LOAD`

   > :point_right: **Внимание!**
   >
   > `GraphQLBootloader::class` должен быть расположен после `RoutesBootloader::class`
   > (устанавливается по умолчанию для проекта [`spiral/app`](https://github.com/spiral/app)).
   > А если говорить точнее, то слой `GraphQLMiddleware::class` должен подключаться
   > после слоя `JsonPayloadMiddleware::class`.
   >
   > Если в вашем приложении не используется `RoutesBootloader::class` и/или в нём
   > отсутствует подключение слоя `JsonPayloadMiddleware::class`, то его следует [подключить
   > самостоятельно](https://spiral.dev/docs/http-middleware/current/en).

## Настройка конфигурационных файлов
Для тонкой настройки GraphQL схемы и использования всех возможностей библиотеки рекомендуется создать
конфигурационный файл:

```shell
# команда создаст файл конфигурации с настройками по умолчанию: config/graphql.php
php app.php graphql:config
```

Для объявления GraphQL перечислений [`EnumType`](https://webonyx.github.io/graphql-php/type-definitions/enums/)
или интерфейсов [`InterfaceType`](https://webonyx.github.io/graphql-php/type-definitions/interfaces/)
с помощью нативных php конструкций, следует [включить анализ](https://spiral.dev/docs/advanced-tokenizer/#class-listeners)
соответствующих файлов.

Для этого укажите  следующие переменные окружения:
- `TOKENIZER_LOAD_ENUMS=true` для анализа php перечислений
- `TOKENIZER_LOAD_INTERFACES=true` для анализа php интерфейсов

Либо создайте/измените файл `config/tokenizer.php`:
```php
return [
    'load' => [
        'classes' => true,
        'enums' => true,
        'interfaces' => true,
    ],
];
```
Проверить текущие настройки можно с помощью команды:
```shell
php app.php tokenizer:info
```

Должны быть включены все загрузчики:

```
+------------+---------+
| Loader     | Status  |
+------------+---------+
| Classes    | enabled |
| Enums      | enabled |
| Interfaces | enabled |
+------------+---------+
```

## Настройка GraphQL схемы

Подробная информация о настройке GraphQL схемы изложена в [документе configure.md](configure.md)

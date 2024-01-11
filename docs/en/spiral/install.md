# GraphQL library for SpiralFramework

## Installation

 Installation [composer](https://getcomposer.org/doc/00-intro.md) package:

```shell
composer require andi-lab/graphql-php-spiral
```

## <a id="bootloader">Bootstrapping settings</a>

Add `Andi\GraphQL\Spiral\Bootloader\GraphQLBootloader` to the list of bootloaders
`App\Application\Kernel::LOAD`

> :point_right: **Attention!**
>
> `GraphQLBootloader::class` must be placed after `RoutesBootloader::class`
> (set by default for project [`spiral/app`](https://github.com/spiral/app)).
> To be more precise, the `GraphQLMiddleware::class` layer should be connected
> after the `JsonPayloadMiddleware::class` layer.
>
> If your application does not use `RoutesBootloader::class` and/or
> there is no connection of the `JsonPayloadMiddleware::class` layer, then it should be
> [connect yourself](https://spiral.dev/docs/http-middleware/current/en).

## Setting up configuration files
To fine-tune the GraphQL schema and use all the capabilities of the library, it is recommended
create a configuration file:

```shell
# the command will create a configuration file with default settings: config/graphql.php
php app.php graphql:config
```

To declare GraphQL enumerations [`EnumType`](https://webonyx.github.io/graphql-php/type-definitions/enums/)
or interfaces [`InterfaceType`](https://webonyx.github.io/graphql-php/type-definitions/interfaces/)
using native PHP constructs, you should [enable analysis](https://spiral.dev/docs/advanced-tokenizer/#class-listeners)
corresponding files.

To do this, specify the following environment variables:
- `TOKENIZER_LOAD_ENUMS=true` for analyzing php enums
- `TOKENIZER_LOAD_INTERFACES=true` for analyzing php interfaces

Or create/modify the file `config/tokenizer.php`:
```php
return [
    'load' => [
        'classes' => true,
        'enums' => true,
        'interfaces' => true,
    ],
];
```
You can check the current settings using the command:
```shell
php app.php tokenizer:info
```

All loaders must be enabled:

```
+------------+---------+
| Loader     | Status  |
+------------+---------+
| Classes    | enabled |
| Enums      | enabled |
| Interfaces | enabled |
+------------+---------+
```

## Setting up a GraphQL schema

Detailed information about setting up a GraphQL schema can be found in [document configure.md](configure.md)

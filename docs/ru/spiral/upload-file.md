# Загрузка файлов

Для загрузки и обработки файлов в вашем приложении, с использованием GraphQL запросов, следует
использовать библиотеку [ecodev/graphql-upload](https://github.com/Ecodev/graphql-upload).

Установка библиотеки осуществляется командой:
```shell
composer require ecodev/graphql-upload
```

Для подключения middleware слоя, отвечающего за загрузку файла, измените конфигурацию middleware слоев.

Пример конфигурации middleware слоев для проекта [`spiral/app`](https://github.com/spiral/app)

```php
namespace App\Application\Bootloader;

use GraphQL\Upload\UploadMiddleware;
use Spiral\Bootloader\Http\RoutesBootloader as BaseRoutesBootloader;
use Spiral\Debug\StateCollector\HttpCollector;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Http\Middleware\JsonPayloadMiddleware;

/**
 * A bootloader that configures the application's routes and middleware.
 *
 * @link https://spiral.dev/docs/http-routing
 */
final class RoutesBootloader extends BaseRoutesBootloader
{
    ...

    protected function globalMiddleware(): array
    {
        return [
            // If you want to automatically detect the user's locale based on the
            // "Accept-Language" header uncomment this middleware and add \Spiral\Bootloader\I18nBootloader
            // to the Kernel
            // LocaleSelector::class,

            ErrorHandlerMiddleware::class,
            JsonPayloadMiddleware::class,
            UploadMiddleware::class,
            HttpCollector::class,
        ];
    }

    ...
}
```

Middleware слой `UploadMiddleware::class` должен быть указан после слоя `JsonPayloadMiddleware::class`
и до `GraphQLMiddleware::class`, который подключается в `GraphQLBootloader` (об этом было написано
в разделе [Установка и настройка](install.md#bootloader)).

Кроме этого, следует зарегистрировать в реестре типов скалярный GraphQL тип `Upload`, определенный в
библиотеке `ecodev/graphql-upload`. Для этого измените [настройки конфигурации](configure.md)
`config/graphql.php`:

```php
return [
    ...

    'additionalTypes' => [
        \GraphQL\Upload\UploadType::class => \Nyholm\Psr7\UploadedFile::class,
    ],
];
```

> :point_right: **Обратите внимание!**
>
> В качестве значения для `UploadType::class` указан псевдоним GraphQL типа
> `\Nyholm\Psr7\UploadedFile::class`. Таким образом библиотека, при определении типа поля
> (в примере ниже), будет автоматически сопоставлять php класс `UploadedFile` с GraphQL типом `Upload`
> (объявлен в классе `\GraphQL\Upload\UploadType::class`).

Пример мутации, принимающей в качестве аргумента загруженный файл:

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use App\GraphQL\Type\File;
use Nyholm\Psr7\UploadedFile;

final class ExampleUploadFile
{
    #[MutationField(type: File::class, mode: TypeAwareInterface::IS_REQUIRED)]
    public function exampleUploadFile(#[Argument] UploadedFile $file): UploadedFile
    {
        return $file;
    }
}
```

Пример вспомогательного объектного GraphQL типа, используемого для отображения информации о
загруженном файле:

```php
namespace App\GraphQL\Type;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Type\AbstractObjectType;
use GraphQL\Type\Definition as Webonyx;
use Nyholm\Psr7\UploadedFile;

final class File extends AbstractObjectType
{
    protected string $name = 'File';

    public function __construct()
    {
        $this->fields = [
            'size' => [
                'type' => Webonyx\IntType::class,
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'resolve' => static fn (UploadedFile $file): int => $file->getSize(),
            ],
            'filename' => [
                'type' => Webonyx\StringType::class,
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'resolve' => static fn (UploadedFile $file): string => $file->getClientFilename(),
            ],
            'mediaType' => [
                'type' => Webonyx\StringType::class,
                'mode' => TypeAwareInterface::IS_REQUIRED,
                'resolve' => static fn (UploadedFile $file): string => $file->getClientMediaType(),
            ],
        ];
    }
}
```

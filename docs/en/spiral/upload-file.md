# Uploading files

To load and process files in your application using GraphQL queries, you should
use the library [ecodev/graphql-upload](https://github.com/Ecodev/graphql-upload).

The library is installed with the command:
```shell
composer require ecodev/graphql-upload
```

To connect the middleware layer responsible for downloading the file, change the configuration of the middleware layers.

Example configuration of middleware layers for a project [`spiral/app`](https://github.com/spiral/app)

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

The middleware layer `UploadMiddleware::class` must be listed after the `JsonPayloadMiddleware::class` layer
and up to `GraphQLMiddleware::class`, which is connected to `GraphQLBootloader` (this was written about
in the [Installation and configuration](install.md#bootloader) section.

In addition, you should register the scalar GraphQL type `Upload`, defined in
library `ecodev/graphql-upload`. To do this, change [configuration settings](configure.md)
`config/graphql.php`:

```php
return [
    ...

    'additionalTypes' => [
        \GraphQL\Upload\UploadType::class => \Nyholm\Psr7\UploadedFile::class,
    ],
];
```

> :point_right: **Note!**
>
> The value for `UploadType::class` is an alias of the GraphQL type
> `\Nyholm\Psr7\UploadedFile::class`. Thus, the library, when defining a field type
> (in the example below), will automatically map the php class `UploadedFile` to the GraphQL type `Upload`
> (declared in class `\GraphQL\Upload\UploadType::class`).

An example of a mutation that takes a loaded file as an argument:

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

An example of a GraphQL object helper type used to display information about
downloaded file:

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

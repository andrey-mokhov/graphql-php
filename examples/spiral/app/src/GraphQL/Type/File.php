<?php

declare(strict_types=1);

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

<?php

declare(strict_types=1);

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

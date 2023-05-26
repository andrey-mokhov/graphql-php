<?php

declare(strict_types=1);

namespace Andi\GraphQL;

use Andi\GraphQL\Exception\NotFoundException;
use GraphQL\Type\Definition as Webonyx;

class TypeRegistry implements TypeRegistryInterface
{
    /**
     * @var array<class-string, non-empty-string>
     */
    protected array $aliases;

    /**
     * @var array<non-empty-string, Webonyx\Type>
     */
    protected array $registry;

    public function __construct()
    {
        $this->aliases = [
            Webonyx\IntType::class     => Webonyx\Type::INT,
            Webonyx\FloatType::class   => Webonyx\Type::FLOAT,
            Webonyx\StringType::class  => Webonyx\Type::STRING,
            Webonyx\BooleanType::class => Webonyx\Type::BOOLEAN,
            Webonyx\IDType::class      => Webonyx\Type::ID,
        ];

        $this->registry = [
            Webonyx\Type::INT     => Webonyx\Type::int(),
            Webonyx\Type::FLOAT   => Webonyx\Type::float(),
            Webonyx\Type::STRING  => Webonyx\Type::string(),
            Webonyx\Type::BOOLEAN => Webonyx\Type::boolean(),
            Webonyx\Type::ID      => Webonyx\Type::id(),
        ];
    }

    /**
     * @param non-empty-string $type
     */
    public function has(string $type): bool
    {
        return isset($this->aliases[$type]) || isset($this->registry[$type]);
    }

    /**
     * @param non-empty-string $type
     *
     * @return Webonyx\Type
     * @throws NotFoundException
     */
    public function get(string $type): Webonyx\Type
    {
        return $this->registry[$this->aliases[$type] ?? $type]
            ?? throw NotFoundException::create($type);
    }
}

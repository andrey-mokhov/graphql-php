<?php

declare(strict_types=1);

namespace Andi\GraphQL;

use Andi\GraphQL\Exception\NotFoundException;
use GraphQL\Type\Definition as Webonyx;

class TypeRegistry implements TypeRegistryInterface
{
    /**
     * @var array<string, string>
     */
    protected array $aliases;

    /**
     * @var array<string, Webonyx\Type>
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

    public function has(string $type): bool
    {
        return isset($this->aliases[$type]) || isset($this->registry[$type]);
    }

    public function get(string $type): Webonyx\Type
    {
        return $this->registry[$this->aliases[$type] ?? $type]
            ?? throw NotFoundException::create($type);
    }

    public function register(Webonyx\Type $type, string ...$aliases): void
    {
        $name = (string) $type;

        $this->registry[$name] = $type;

        foreach ($aliases as $alias) {
            $this->aliases[$alias] = $name;
        }
    }

    public function __invoke(string $type): Webonyx\Type
    {
        return $this->get($type);
    }

    /**
     * @return iterable<Webonyx\ObjectType>
     */
    public function getTypes(): iterable
    {
        foreach ($this->registry as $type) {
            if ($type instanceof Webonyx\ObjectType && ! empty($type->getInterfaces())) {
                yield $type;
            }
        }
    }
}

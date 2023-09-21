<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use Andi\GraphQL\Argument\Argument;
use Andi\GraphQL\Definition\Field\ArgumentInterface;
use Andi\GraphQL\Definition\Field\ArgumentsAwareInterface;
use Andi\GraphQL\Definition\Field\ObjectFieldInterface;
use GraphQL\Type\Definition as Webonyx;

abstract class AbstractObjectField implements ObjectFieldInterface, ArgumentsAwareInterface
{
    protected string $name;

    protected string $description;

    protected string $type;

    protected int $typeMode;

    protected string $deprecationReason;

    protected iterable $arguments;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason ?? null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeMode(): int
    {
        return $this->typeMode ?? 0;
    }

    public function getArguments(): iterable
    {
        foreach ($this->arguments ?? [] as $name => $argument) {
            if ($argument instanceof ArgumentInterface) {
                yield $argument;
            } elseif (is_string($argument)) {
                yield new Argument($name, $argument);
            } elseif (is_array($argument)) {
                if (is_string($name)) {
                    $argument['name'] ??= $name;
                }

                if (! isset($argument['name'], $argument['type'])) {
                    yield $argument;
                }

                if ($argument['type'] instanceof Webonyx\Type) {
                    yield $argument;
                }

                $parameters = [
                    'name' => $argument['name'],
                    'type' => $argument['type'],
                    'typeMode' => $argument['typeMode'] ?? 0,
                    'description' => $argument['description'] ?? null,
                    'deprecationReason'=> $argument['deprecationReason'] ?? null,
                ];

                if (isset($argument['defaultValue']) || array_key_exists('defaultValue', $argument)) {
                    $parameters['defaultValue'] = $argument['defaultValue'];
                }

                yield new Argument(...$parameters);
            }
        }
    }
}

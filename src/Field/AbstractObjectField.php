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
    use FieldExtractorTrait;

    protected string $name;

    protected string $description;

    protected string $type;

    protected int $mode;

    protected string $deprecationReason;

    protected iterable $arguments;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->description ?? null;
    }

    public function getDeprecationReason(): ?string
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->deprecationReason ?? null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMode(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->mode ?? 0;
    }

    public function getArguments(): iterable
    {
        /**
         * @psalm-suppress RedundantPropertyInitializationCheck
         * @psalm-suppress RedundantCondition
         * @psalm-suppress TypeDoesNotContainType
         */
        foreach ($this->arguments ?? [] as $name => $argument) {
            if ($argument instanceof ArgumentInterface || $argument instanceof Webonyx\Type) {
                yield $argument;
            } elseif (is_string($argument)) {
                yield new Argument($name, $argument);
            } elseif (is_array($argument)) {
                if (is_string($name)) {
                    $argument['name'] ??= $name;
                }

                yield $this->extract($argument, Argument::class);
            }
        }
    }
}

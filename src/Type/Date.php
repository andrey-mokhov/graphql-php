<?php

declare(strict_types=1);

namespace Andi\GraphQL\Type;

use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\Printer;
use GraphQL\Utils\Utils;

final class Date extends AbstractScalarType
{
    protected string $name = 'Date';

    protected string $description = <<<'STR'
        The `Date` scalar type represents date data,
        represented as an ISO-8601 encoded UTC date string.';
        STR;

    public function serialize(mixed $value): string
    {
        if (! $value instanceof \DateTimeInterface) {
            throw new InvariantViolation(
                'Date is not an instance of DateTimeInterface: ' . Utils::printSafe($value)
            );
        }

        return $value->format('Y-m-d');
    }

    public function parseValue(mixed $value): ?\DateTimeImmutable
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        $str = (string) $value;

        $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d', $str, new \DateTimeZone('UTC'));

        $errors = \DateTimeImmutable::getLastErrors() ?: ['error_count' => 0, 'warnings' => []];

        if ($errors['error_count'] > 0 || \count($errors['warnings'])) {
            throw new Error(\sprintf(
                'The Date value must be a string value in "Y-m-d" format; given: %s',
                Utils::printSafeJson($value),
            ));
        }
        \assert($dateTime instanceof \DateTimeImmutable);
        return $dateTime;
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null): \DateTimeImmutable
    {
        if ($valueNode instanceof StringValueNode) {
            $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d', $valueNode->value, new \DateTimeZone('UTC'));

            $errors = \DateTimeImmutable::getLastErrors() ?: ['error_count' => 0, 'warnings' => []];

            if ($errors['error_count'] > 0 || \count($errors['warnings'])) {
                throw new Error(
                    \sprintf('Invalid Date value; given: %s', Printer::doPrint($valueNode)),
                    $valueNode,
                );
            }
            \assert($dateTime instanceof \DateTimeImmutable);
            return $dateTime;
        }

        throw new Error(\sprintf(
            'The Date value must be a string value in "Y-m-d" format; given: %s',
            Printer::doPrint($valueNode),
        ));
    }
}

<?php

declare(strict_types=1);

namespace Andi\GraphQL\Common;

use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition as Webonyx;

final class LazyParserType
{
    public function __construct(
        private readonly string $type,
        private readonly int $mode,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function __invoke(): Webonyx\Type
    {
        if (! $this->typeRegistry->has($this->type)) {
            return $this->getType(Parser::parseType($this->type));
        }

        $type = $this->typeRegistry->get($this->type);

        if (TypeAwareInterface::ITEM_IS_REQUIRED === (TypeAwareInterface::ITEM_IS_REQUIRED & $this->mode)) {
            $type = Webonyx\Type::nonNull($type);
        }

        if (TypeAwareInterface::IS_LIST === (TypeAwareInterface::IS_LIST & $this->mode)) {
            $type = Webonyx\Type::listOf($type);
        }

        if (TypeAwareInterface::IS_REQUIRED === (TypeAwareInterface::IS_REQUIRED & $this->mode)) {
            $type = Webonyx\Type::nonNull($type);
        }

        return $type;
    }

    private function getType(Node&TypeNode $node): Webonyx\Type
    {
        if ($node instanceof NameNode) {
            return $this->typeRegistry->get($node->value);
        }

        if ($node instanceof NamedTypeNode) {
            return $this->getType($node->name);
        }

        if ($node instanceof NonNullTypeNode) {
            $type = $this->getType($node->type);
            return $type instanceof Webonyx\NullableType
                ? Webonyx\Type::nonNull($type)
                : $type;
        }

        if ($node instanceof ListTypeNode) {
            return Webonyx\Type::listOf($this->getType($node->type));
        }

        throw new CantResolveGraphQLTypeException(sprintf('Can\'t resolve GraphQL type for "%s"', $this->type));
    }
}

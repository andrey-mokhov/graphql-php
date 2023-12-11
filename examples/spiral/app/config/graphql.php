<?php

declare(strict_types=1);

use Andi\GraphQL\ArgumentResolver\Middleware as Argument;
use Andi\GraphQL\InputObjectFieldResolver\Middleware as Inputs;
use Andi\GraphQL\ObjectFieldResolver\Middleware as Objects;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\TypeResolver\Middleware as Types;
use App\GraphQL\Factory\RootValueFactory;

return [
    'url' => getenv('GRAPHQL_URL') ?: '/api/graphql',
    'queryType' => GraphQLConfig::DEFAULT_QUERY_TYPE,
    'mutationType' => GraphQLConfig::DEFAULT_MUTATION_TYPE,
    'rootValue' => RootValueFactory::class,
    'context' => null,
    'debugFlag' => null,

    'typeResolverMiddlewares' => [
        Types\EnumTypeMiddleware::class => Types\EnumTypeMiddleware::PRIORITY,
        Types\WebonyxGraphQLTypeMiddleware::class => Types\WebonyxGraphQLTypeMiddleware::PRIORITY,
        Types\GraphQLTypeMiddleware::class => Types\GraphQLTypeMiddleware::PRIORITY,
        Types\AttributedGraphQLTypeMiddleware::class => Types\AttributedGraphQLTypeMiddleware::PRIORITY,
    ],

    'objectFieldResolverMiddlewares' => [
        Objects\QueryFieldByReflectionMethodMiddleware::class => Objects\QueryFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\MutationFieldByReflectionMethodMiddleware::class => Objects\MutationFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\AdditionalFieldByReflectionMethodMiddleware::class => Objects\AdditionalFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\InterfaceFieldByReflectionMethodMiddleware::class => Objects\InterfaceFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\ObjectFieldByReflectionMethodMiddleware::class => Objects\ObjectFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\ObjectFieldByReflectionPropertyMiddleware::class => Objects\ObjectFieldByReflectionPropertyMiddleware::PRIORITY,
        Objects\ObjectFieldMiddleware::class => Objects\ObjectFieldMiddleware::PRIORITY,
        Objects\WebonyxObjectFieldMiddleware::class => Objects\WebonyxObjectFieldMiddleware::PRIORITY,
    ],

    'inputObjectFieldResolverMiddlewares' => [
        Inputs\ReflectionPropertyMiddleware::class => Inputs\ReflectionPropertyMiddleware::PRIORITY,
        Inputs\ReflectionMethodMiddleware::class => Inputs\ReflectionMethodMiddleware::PRIORITY,
        Inputs\InputObjectFieldMiddleware::class => Inputs\InputObjectFieldMiddleware::PRIORITY,
        Inputs\WebonyxInputObjectFieldMiddleware::class => Inputs\WebonyxInputObjectFieldMiddleware::PRIORITY,
    ],

    'argumentResolverMiddlewares' => [
        Argument\ReflectionParameterMiddleware::class => Argument\ReflectionParameterMiddleware::PRIORITY,
        Argument\ArgumentMiddleware::class => Argument\ArgumentMiddleware::PRIORITY,
        Argument\ArgumentConfigurationMiddleware::class => Argument\ArgumentConfigurationMiddleware::PRIORITY,
    ],

    'additionalTypes' => [
    ],
];

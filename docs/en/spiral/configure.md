# Настройка GraphQL схемы

Конфигурационный файл `config/graphql.php` содержит следующие настройки:

```php
use Andi\GraphQL\ArgumentResolver\Middleware as Argument;
use Andi\GraphQL\InputObjectFieldResolver\Middleware as Inputs;
use Andi\GraphQL\ObjectFieldResolver\Middleware as Objects;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\TypeResolver\Middleware as Types;

return [
    /**
     * URL address to which the middleware layer will respond
     */
    'url' => getenv('GRAPHQL_URL') ?: '/api/graphql',

    /**
     * The name of the GraphQL object type used to process query requests, defaults to `Query`.
     * For 'Query' type use: Andi\GraphQL\Type\QueryType::class
     * It is acceptable to use an FQCN record, for example: App\GraphQL\Type\Query::class
     */
    'queryType' => GraphQLConfig::DEFAULT_QUERY_TYPE,

    /**
     * By default, mutation requests are disabled!
     * To enable processing of mutation requests, you must specify the name of the corresponding GraphQL type.
     * For 'Mutation' type (corresponds to the value of the GraphQLConfig::DEFAULT_MUTATION_TYPE constant)
     * used: Andi\GraphQL\Type\MutationType::class
     * It is acceptable to use an FQCN record, for example: App\GraphQL\Type\Mutation::class
     */
    'mutationType' => null,

    /**
     * The name of the class whose instance will be used as the root value when calling
     * field handlers for Query and Mutation types.
     *
     * If a class implements the __invoke magic method, then the root value will be
     * the result of calling this method is used.
     *
     * The __invoke method can contain any parameters, provided that the DI container can create
     * instances of classes for them. In addition, the method can use the following parameters:
     *   - string $operationType the corresponding value (query or mutation) will be set
     *     GraphQL request type.
     *     Important! The parameter should be named exactly like this: $operationType;
     *   - GraphQL\Server\OperationParams $params GraphQL query parameters;
     *   - GraphQL\Language\AST\DocumentNode $doc GraphQL query as an AST document
     */
    'rootValue' => null,

    /**
     * The name of the class whose instance will be used as the calling context and passed
     * into each GraphQL handler.
     *
     * If a class implements the __invoke magic method, then the context will be used
     * the result of calling this method.
     *
     * The __invoke method can contain any parameters, provided that the DI container can create
     * instances of classes for them. In addition, the method can use the following parameters:
     *   - string $operationType the corresponding value (query or mutation) will be set
     *     GraphQL request type.
     *     Important! The parameter should be named exactly like this: $operationType;
     *   - GraphQL\Server\OperationParams $params GraphQL query parameters;
     *   - GraphQL\Language\AST\DocumentNode $doc GraphQL query as an AST document
     */
    'context' => null,

    /**
     * Level of detail of errors encountered while processing a GraphQL request.
     *
     * Default: GraphQL\Error\DebugFlag::INCLUDE_DEBUG_MESSAGE
     *
     * @see https://webonyx.github.io/graphql-php/class-reference/#graphqlserverserverconfig-methods
     */
    'debugFlag' => null,

    /**
     * Defining a GraphQL data type using pipeline middleware,
     * based on a priority queue.
     *
     * The name of the middleware layer is determined by the array key, and the execution priority is determined by the value. How
     * the higher the priority value, the earlier the middleware layer will be called. Middleware layers,
     * whose priorities are equal will be called in the order of their declaration in this array (the earlier specified,
     * the sooner it will be called).
     *
     * These rules are valid for subsequent middleware pipelines.
     */
    'typeResolverMiddlewares' => [
        Types\EnumTypeMiddleware::class => Types\EnumTypeMiddleware::PRIORITY,
        Types\WebonyxGraphQLTypeMiddleware::class => Types\WebonyxGraphQLTypeMiddleware::PRIORITY,
        Types\GraphQLTypeMiddleware::class => Types\GraphQLTypeMiddleware::PRIORITY,
        Types\AttributedGraphQLTypeMiddleware::class => Types\AttributedGraphQLTypeMiddleware::PRIORITY,
    ],

    /**
     * Definition of a field (FieldDefinition) for an object or interface GraphQL type
     * (ObjectType or InterfaceType) using pipeline middleware based
     * on a priority queue.
     */
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

    /**
     * Defining a field (InputObjectField) for the incoming GraphQL object type (InputObjectType)
     * using pipeline middleware based on a priority queue.
     */
    'inputObjectFieldResolverMiddlewares' => [
        Inputs\ReflectionPropertyMiddleware::class => Inputs\ReflectionPropertyMiddleware::PRIORITY,
        Inputs\ReflectionMethodMiddleware::class => Inputs\ReflectionMethodMiddleware::PRIORITY,
        Inputs\InputObjectFieldMiddleware::class => Inputs\InputObjectFieldMiddleware::PRIORITY,
        Inputs\WebonyxInputObjectFieldMiddleware::class => Inputs\WebonyxInputObjectFieldMiddleware::PRIORITY,
    ],

    /**
     * Defining Argument Configuration for GraphQL Object or Interface Type Fields
     * using pipeline middleware based on a priority queue.
     */
    'argumentResolverMiddlewares' => [
        Argument\ReflectionParameterMiddleware::class => Argument\ReflectionParameterMiddleware::PRIORITY,
        Argument\ArgumentMiddleware::class => Argument\ArgumentMiddleware::PRIORITY,
        Argument\ArgumentConfigurationMiddleware::class => Argument\ArgumentConfigurationMiddleware::PRIORITY,
    ],

    /**
     * List of additional GraphQL types not directly related to the application
     * (for example, declared in third-party libraries).
     *
     * For example: [Andi\GraphQL\Type\DateTime::class, Andi\GraphQL\Type\Date::class]
     */
    'additionalTypes' => [
    ],
];
```

> By default, there is no configuration file, it can be created [console command](install.md#setting-configuration-files).

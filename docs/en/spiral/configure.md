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
     * URL адрес на вызов которого будет реагировать middleware слой
     */
    'url'          => getenv('GRAPHQL_URL') ?: '/api/graphql',

    /**
     * Имя объектного GraphQL типа, используемого для обработки query запросов, по умолчанию `Query`.
     * Для 'Query' типа используется: Andi\GraphQL\Type\QueryType::class
     * Допустимо использование FQCN записи, например: App\GraphQL\Type\Query::class
     */
    'queryType'    => GraphQLConfig::DEFAULT_QUERY_TYPE,

    /**
     * По умолчанию mutation запросы отключены!
     * Для включения обработки mutation запросов следует указать имя соответствующего GraphQL типа.
     * Для 'Mutation' типа (соответствует значению константы GraphQLConfig::DEFAULT_MUTATION_TYPE)
     * используется: Andi\GraphQL\Type\MutationType::class
     * Допустимо использование FQCN записи, например: App\GraphQL\Type\Mutation::class
     */
    'mutationType' => null,

    /**
     * Имя сервиса, экземпляр которого будет использован в качестве корневого значения при вызове
     * обработчиков полей для GraphQL типа Query.
     * Экземпляр класса должен быть выставлен в скопе DI-контейнера
     * до вызова Andi\GraphQL\Spiral\Middleware\GraphQLMiddleware::class
     */
    'rootValue' => null,

    /**
     * Имя сервиса, экземпляр которого будет использован в качестве контекста вызова и передан
     * в каждый GraphQL обработчик.
     * Экземпляр класса должен быть выставлен в скопе DI-контейнера
     * до вызова Andi\GraphQL\Spiral\Middleware\GraphQLMiddleware::class
     *
     * Например: Spiral\Auth\AuthContext::class
     */
    'context'      => null,

    /**
     * Определение GraphQL типа данных с использованием middleware конвеера,
     * основанного на приоритезированной очереди.
     *
     * Имя middleware слоя определяется ключем массива, а приоритет исполнения - значением. Чем
     * больше значение приоритета, тем раньше будет вызван middleware слой. Middleware слои,
     * приоритеты которых равны, будут вызваны в порядке их объявления в данном массиве (чем раньше указан,
     * тем раньше будет вызван).
     *
     * Данные правила справедливы для последующих middleware конвееров.
     */
    'typeResolverMiddlewares' => [
        Types\EnumTypeMiddleware::class              => Types\EnumTypeMiddleware::PRIORITY,
        Types\WebonyxGraphQLTypeMiddleware::class    => Types\WebonyxGraphQLTypeMiddleware::PRIORITY,
        Types\GraphQLTypeMiddleware::class           => Types\GraphQLTypeMiddleware::PRIORITY,
        Types\AttributedGraphQLTypeMiddleware::class => Types\AttributedGraphQLTypeMiddleware::PRIORITY,
    ],

    /**
     * Определение поля (FieldDefinition) для объектного или интерфейсного GraphQL типа
     * (ObjectType или InterfaceType) с использованием middleware конвеера, основанного
     * на приоритизированной очереди.
     */
    'objectFieldResolverMiddlewares' => [
        Objects\QueryFieldByReflectionMethodMiddleware::class      => Objects\QueryFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\MutationFieldByReflectionMethodMiddleware::class   => Objects\MutationFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\AdditionalFieldByReflectionMethodMiddleware::class => Objects\AdditionalFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\InterfaceFieldByReflectionMethodMiddleware::class  => Objects\InterfaceFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\ObjectFieldByReflectionMethodMiddleware::class     => Objects\ObjectFieldByReflectionMethodMiddleware::PRIORITY,
        Objects\ObjectFieldByReflectionPropertyMiddleware::class   => Objects\ObjectFieldByReflectionPropertyMiddleware::PRIORITY,
        Objects\ObjectFieldMiddleware::class                       => Objects\ObjectFieldMiddleware::PRIORITY,
        Objects\WebonyxObjectFieldMiddleware::class                => Objects\WebonyxObjectFieldMiddleware::PRIORITY,
    ],

    /**
     * Определение поля (InputObjectField) для входящего объектного GraphQL типа (InputObjectType)
     * с использованием middleware конвеера, основанного на приоритизированной очереди.
     */
    'inputObjectFieldResolverMiddlewares' => [
        Inputs\ReflectionPropertyMiddleware::class      => Inputs\ReflectionPropertyMiddleware::PRIORITY,
        Inputs\ReflectionMethodMiddleware::class        => Inputs\ReflectionMethodMiddleware::PRIORITY,
        Inputs\InputObjectFieldMiddleware::class        => Inputs\InputObjectFieldMiddleware::PRIORITY,
        Inputs\WebonyxInputObjectFieldMiddleware::class => Inputs\WebonyxInputObjectFieldMiddleware::PRIORITY,
    ],

    /**
     * Определение конфигурации аргумента объектных или интерфейсных полей GraphQL типа
     * с использованием middleware конвеера, основанного на приоритизированной очереди.
     */
    'argumentResolverMiddlewares' => [
        Argument\ReflectionParameterMiddleware::class   => Argument\ReflectionParameterMiddleware::PRIORITY,
        Argument\ArgumentMiddleware::class              => Argument\ArgumentMiddleware::PRIORITY,
        Argument\ArgumentConfigurationMiddleware::class => Argument\ArgumentConfigurationMiddleware::PRIORITY,
    ],

    /**
     * Перечень дополнительных GraphQL типов, не относящихся непосредственно к приложению
     * (например объявленных в сторонних библиотеках).
     *
     * Например: [Andi\GraphQL\Type\DateTime::class, Andi\GraphQL\Type\Date::class]
     */
    'additionalTypes' => [
    ],
];
```

> По умолчанию конфигурационный файл отсутствует, его можно создать [консольной командой](install.md#настройка-конфигурационных-файлов).

# Введение

Для query запросов используется `Andi\GraphQL\Type\QueryType`, устанавливается в [настройках конфигурации](configure.md).
Можно задать свою реализацию `Query` типа.

Для mutation запросов используется `Andi\GraphQL\Type\MutationType`, по умолчанию [отключен в конфигурации](configure.md).
Можно задать свою реализацию `Mutation` типа.

## Определение Query полей

Query поля можно задать:
- с помощью атрибута `Andi\GraphQL\Attribute\QueryField`. Применим только к методам классов;
- путем реализации интерфейса `Andi\GraphQL\Field\QueryFieldInterface`.

### Определение Query поля с помощью атрибута `#[QueryField]`

Аргументами поля являются параметры метода помеченные атрибутом `Andi\GraphQL\Attribute\Argument`.

### Определение Query поля с помощью интерфейса `QueryFieldInterface`

Вспомогательные интерфейсы при определение Query поля:

<dl>
    <dt><code>Andi\GraphQL\Definition\Field\ArgumentsAwareInterface</code></dt>
    <dd>Реализация интерфейса позволяет определеть список аргументов Query поля.</dd>
    <dt><code>Andi\GraphQL\Definition\Field\ResolveAwareInterface</code></dt>
    <dd>Реализация интерфейса позволяет определеть метод, вычисляющий значение Query поля.</dd>
    <dt><code>Andi\GraphQL\Definition\Field\ComplexityAwareInterface</code></dt>
    <dd>
        Реализация интерфейса позволяет определеть метод, используемый для ограничения сложности запроса.
        Подробнее в разделе <a href="https://webonyx.github.io/graphql-php/security/#query-complexity-analysis">Security</a>.
    </dd>
</dl>

## Определение Mutation полей

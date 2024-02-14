<p align="center">
    <a href="https://github.com/andrey-mokhov/graphql-php/actions"><img src="https://github.com/andrey-mokhov/graphql-php/workflows/build/badge.svg" /></a>
    <a href="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/?branch=master"><img src="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality" /></a>
    <a href="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/?branch=master"><img src="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/badges/coverage.png?b=master" alt="Code Coverage" /></a>
    <a href="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/build-status/master"><img src="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/badges/build.png?b=master" alt="Build Status" /></a>
    <a href="https://scrutinizer-ci.com/code-intelligence"><img src="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/badges/code-intelligence.svg?b=master" alt="Code Intelligence Status" /></a>
    <a href="https://shepherd.dev/github/andrey-mokhov/graphql-php"><img src="https://shepherd.dev/github/andrey-mokhov/graphql-php/coverage.svg" alt="Code Coverage" /></a>
    <a href="https://shepherd.dev/github/andrey-mokhov/graphql-php"><img src="https://shepherd.dev/github/andrey-mokhov/graphql-php/level.svg" alt="Psalm Level" /></a>
</p>

# GraphQL library

The GraphQL library for PHP allows you to define a GraphQL API using attributes, interfaces,
and prepared abstract classes.

## Features

Key features of the library include:
- framework agnostic, currently integrated with SpiralFramework on RoadRunner;
- support code first & schema first principles, which can simultaneously complement each other;
- extending via middleware layers.

## Simple Example

```php
use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Attribute\QueryField;

final class SimpleController
{
    #[QueryField(name: 'echo')]
    #[MutationField(name: 'echo')]
    public function echoMessage(#[Argument] string $message): string
    {
        return 'echo: ' . $message;
    }
}
```

This example shows how easy it is to define Query & Mutation. At the same time, the library
provides full control when defining a GraphQL schema: custom types, required fields,
default values, and much more.

## Documentation

Documentation is currently available in:
- [English](docs/en/index.md)
- [Russian](docs/ru/index.md)

## Examples

The library [includes examples](examples) of defining a GraphQL schema. All definitions mentioned
in the documentation are present in the examples in one form or another.

## License

See [LICENSE](../../../LICENSE)

## Community

Telegram group: [GraphQL library for PHP](https://t.me/andi_lab_graphql)

## Roadmap

- [x] release 1.0
  - [x] documentation of the development of middleware layers, with an example
  - [x] documentation and example of connecting the
    [`ecodev/graphql-upload`](https://github.com/Ecodev/graphql-upload) library for uploading files
  - [ ] support multiple schemas for `andi-lab/graphql-php-spiral`
- [ ] release 1.1
  - [ ] mockup types and fields
- [ ] other release
  - [ ] inputs validate
  - [ ] integration with Apollo Federation


<p align="center">
    <a href="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/?branch=master"><img src="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality" /></a>
    <a href="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/?branch=master"><img src="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/badges/coverage.png?b=master" alt="Code Coverage" /></a>
    <a href="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/build-status/master"><img src="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/badges/build.png?b=master" alt="Build Status" /></a>
    <a href="https://scrutinizer-ci.com/code-intelligence"><img src="https://scrutinizer-ci.com/g/andrey-mokhov/graphql-php/badges/code-intelligence.svg?b=master" alt="Code Intelligence Status" /></a>
</p>

# GraphQL library

Russian [documentations](docs/ru/index.md)

# roadmap
release 0.1
- [x] Type resolver
  - [x] UnionType resolver
  - [x] InterfaceType resolver
  - [x] EnumType resolver
  - [x] ScalarType resolver
  - [x] ObjectType resolver
  - [x] InputObjectType resolver
- [x] Dynamic UnionType
- [x] Attribute `QueryField` for any service methods
- [x] Attribute `MutationField` for any service methods
- [x] Attribute `AdditionalField` for any service methods - add additional field to `ObjectType`, `InterfaceType`
- [x] scalar DateTime type
- [x] scalar Date type

release 1.0
- [x] Unit tests
- [ ] Functional tests
- [x] implementation technical debt
- [ ] Abstract classes for base definitions
  - [ ] ObjectType
  - [ ] InputObjectType
  - [ ] Interface
  - [ ] UnionType
  - [ ] EnumType
  - [x] ScalarType
- [ ] documentation and examples
- [x] extract type's properties description  from  contructor's docBlock
- [x] UploadFile type (see https://github.com/Ecodev/graphql-upload)

release 1.1
- [ ] Mockup types and fields

release 1.2
- [ ] inputs validate

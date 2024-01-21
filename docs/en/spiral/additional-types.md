# Connecting additional GraphQL types

The library provides the ability to register additional GraphQL types in the registry for their
later use in your application. This is useful if you need to use
third party libraries.

For automatic registration, you can [expand the list of directories](https://spiral.dev/docs/advanced-tokenizer/current#customizing-search-directories),
which Tokenizer will scan or list GraphQL types in the configuration yourself.

Configuration example `config/graphql.php`:

```php
return [
    ...

    'additionalTypes' => [
        \GraphQL\Upload\UploadType::class => \Nyholm\Psr7\UploadedFile::class,
        \Andi\GraphQL\Type\DateTime::class => [\DateTimeImmutable::class, \DateTimeInterface::class],
        \Andi\GraphQL\Type\Date::class,
    ],
];
```

The `'additionalTypes'` configuration option must be an array and contain values with numeric or
string keys. If the key is a number, then the value is expected to be a string - the name of the class,
defining a GraphQL type. If the key is a string, then the key is interpreted by the library as a class name,
defining a GraphQL type, and the key value(s) as an alias(s) of the GraphQL type, and aliases can
be several.

Aliases are used by the library to automatically match GraphQL types to the appropriate
method/argument definition.

```php
namespace App\GraphQL\Field;

use Andi\GraphQL\Attribute\QueryField;

final class SimpleService
{
    ...

    #[QueryField]
    public function currentTime(): \DateTimeInterface
    {
        return new \DateTimeImmutable();
    }
}
```

In the example above, the library will automatically detect the field type as `DateTimeInterface` and
the alias specified in the registry will find the corresponding GraphQL field type - `DateTime`, defined
in class `Andi\GraphQL\Type\DateTime`.

## <a id="date-time">Scalar type `DateTime`</a>

The library includes a scalar GraphQL type `DateTime` defined in the class
`Andi\GraphQL\Type\DateTime`. When using this type in a GraphQL request (as an argument
or fields of an InputObjectType), it is acceptable to use string values in the following formats:
- `Y-m-d` - record the day in ISO-8601 format, in this case the hours, minutes and seconds will be set in
  zero value, and time zone `UTC`;
- `Y-m-d\TH:i:s` - record date-time in ISO-8601 format without time zone, in this case time zone
  will be set to `UTC`;
- `Y-m-d\TH:i:sP` - full record of the date and time with the specified time zone in ISO-8601 format.

At the same time, for parameters of php methods, values of class properties, the string passed in the GraphQL request
the value will be converted to a `\DateTimeImmutable` object.

> :point_right: **Important!** :point_left:
>
> Do not use the class `\DateTime` as a type for method parameters, class properties, or
> method return value type. Instead use `\DateTimeImmutable` or
> `\DateTimeInterface`.

By default, this GraphQL scalar type is not registered in the type registry. It's necessary
connect using one of the methods indicated at the beginning of this article.

## <a id="date">Scalar type `Date`</a>

The library includes a scalar GraphQL type `Date` defined in the class
`Andi\GraphQL\Type\Date`. When using this type in a GraphQL request (as an argument
or fields of an incoming object type), string values of the `Y-m-d` format are acceptable.

At the same time, for parameters of php methods, values of class properties, the string passed in the GraphQL request
the value will be converted to a `\DateTimeImmutable` object. In this case, hours, minutes and seconds will have
zero values, and the time zone is `UTC`.

> :point_right: **Important!** :point_left:
>
> As with the scalar GraphQL type `DateTime`, do not use the php class `DateTime`.

By default, this GraphQL scalar type is not registered in the type registry. It's necessary
connect using one of the methods indicated at the beginning of this article.

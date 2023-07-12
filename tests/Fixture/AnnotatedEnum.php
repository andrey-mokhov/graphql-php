<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Fixture;
/**
 * AnnotatedEnum description.
 *
 * @internal
 * @psalm-internal Andi\Tests
*/
enum AnnotatedEnum
{
    /**
     * Foo case description.
     *
     * @deprecated Foo case is deprecated.
     */
    case foo;
}

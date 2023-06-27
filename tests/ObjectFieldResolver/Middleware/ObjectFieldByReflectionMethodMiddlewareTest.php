<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\ObjectFieldResolver\Middleware;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AbstractFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AbstractObjectFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\MiddlewareInterface;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\InvokerInterface;

#[CoversClass(AbstractObjectFieldByReflectionMethodMiddleware::class)]
#[CoversClass(AbstractFieldByReflectionMethodMiddleware::class)]
#[UsesClass(ArgumentResolver::class)]
#[UsesClass(ArgumentConfigurationMiddleware::class)]
#[UsesClass(TypeRegistry::class)]
class ObjectFieldByReflectionMethodMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ObjectFieldByReflectionMethodMiddleware $middleware;

    private TypeRegistryInterface $typeRegistry;

    private ReaderInterface $reader;

    private InvokerInterface $invoker;

    protected function setUp(): void
    {
        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ArgumentConfigurationMiddleware());

        $this->middleware = new ObjectFieldByReflectionMethodMiddleware(
            $this->reader = \Mockery::mock(ReaderInterface::class),
            $this->typeRegistry = new TypeRegistry(),
            $argumentResolver,
            $this->invoker = \Mockery::mock(InvokerInterface::class),
        );

    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(MiddlewareInterface::class, $this->middleware);
    }

    public function testCallNextResolver(): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->once()->andReturn(new Webonyx\FieldDefinition(['name' => 'foo']));

        $this->middleware->process(null, $nextResolver);
    }

    // #[DataProvider('getDataForProcess')]
    public function _testProcess(array $expected, object $object, string $exception = null): void
    {
        $nextResolver = \Mockery::mock(ObjectFieldResolverInterface::class);
        $nextResolver->shouldReceive('resolve')->never();

        $info = \Mockery::mock(Webonyx\ResolveInfo::class);

        $field = null;
        $reflectionClass = new \ReflectionClass($object);
        foreach ($reflectionClass->getMethods() as $field) {
            break;
        }

        $objectField = $this->middleware->process($field, $nextResolver);

        self::assertInstanceOf(Webonyx\FieldDefinition::class, $objectField);

        self::assertSame($expected['name'], $objectField->getName());
        self::assertSame($expected['description'] ?? null, $objectField->description);
        self::assertSame($expected['deprecationReason'] ?? null, $objectField->deprecationReason);

        $type = $objectField->getType();
        if ($type instanceof Webonyx\WrappingType) {
            $type = $type->getWrappedType();
        }
        self::assertSame($expected['type'], $type);

        if (isset($expected['arguments'])) {
            foreach ($expected['arguments'] as $name => $type) {
                $argument = $objectField->getArg($name);

                $argumentType = $argument->getType();
                if ($argumentType instanceof Webonyx\WrappingType) {
                    $argumentType = $argumentType->getWrappedType();
                }

                self::assertSame($type, $argumentType);
            }
        }

        if (isset($expected['resolve'])) {
            self::assertIsCallable($objectField->resolveFn);
            self::assertSame($expected['resolve'], call_user_func($objectField->resolveFn, null, [], null, $info));
        }

        if (isset($expected['complexity'])) {
            self::assertIsCallable($objectField->complexityFn);
            self::assertSame($expected['complexity'], call_user_func($objectField->complexityFn, 0, []));
        }
    }
}

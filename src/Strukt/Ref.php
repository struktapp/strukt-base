<?php

namespace Strukt;

use Closure;

/** Provides cached reflection wrappers for functions, classes, methods, and properties. */
final class Ref
{
    /** @var array<string, \ReflectionClass<object>> Reflected classes by name. */
    private static array $classes = [];

    /** Reflected class or object description. */
    private \ReflectionClass $class;

    /** Object used for instance method and property operations. */
    private ?object $instance = null;

    /**
     * @param \ReflectionClass<object> $class Reflected class.
     */
    private function __construct(\ReflectionClass $class)
    {
        $this->class = $class;
    }

    /**
     * Creates a callable reference.
     *
     * @param callable $function Function or callable object.
     * @return FunctionReference Callable reference.
     */
    public static function func(callable $function): FunctionReference
    {
        if (is_string($function)) {
            try {
                return new FunctionReference(new \ReflectionFunction($function));
            } catch (\ReflectionException) {
                // Closure::fromCallable below provides the native callable error.
            }
        }

        $closure = $function instanceof Closure ? $function : Closure::fromCallable($function);

        return new FunctionReference(new \ReflectionFunction($closure));
    }

    /**
     * Creates a reference from an object instance.
     *
     * @param object $instance Instance to inspect.
     * @return static Object reference.
     */
    public static function createFrom(object $instance): static
    {
        $reference = new static(self::classFor($instance::class));
        $reference->instance = $instance;

        return $reference;
    }

    /**
     * Creates a reference from a class name.
     *
     * @param class-string $className Class to inspect.
     * @return static Class reference.
     */
    public static function create(string $className): static
    {
        return new static(self::classFor($className));
    }

    /**
     * Returns a method reference.
     *
     * @param string $name Method name.
     * @return MethodReference Method reference.
     * @throws \LogicException When an instance method has no instance.
     */
    public function method(string $name): MethodReference
    {
        $method = $this->class->getMethod($name);
        if (!$method->isStatic() && $this->instance === null) {
            throw new \LogicException(sprintf('Method [%s] requires an object instance.', $name));
        }

        return new MethodReference($method, $this->instance);
    }

    /**
     * Lists all properties visible to reflection.
     *
     * @return list<string> Property names.
     */
    public function props(): array
    {
        return array_map(
            static fn (\ReflectionProperty $property): string => $property->getName(),
            $this->class->getProperties(),
        );
    }

    /**
     * Returns a property reference.
     *
     * @param string $name Property name.
     * @return PropertyReference Property reference.
     * @throws \LogicException When an instance property has no instance.
     */
    public function prop(string $name): PropertyReference
    {
        $property = $this->class->getProperty($name);
        if (!$property->isStatic() && $this->instance === null) {
            throw new \LogicException(sprintf('Property [%s] requires an object instance.', $name));
        }

        return new PropertyReference($property, $this->instance);
    }

    /**
     * Returns the class reflection object.
     *
     * @return \ReflectionClass<object> Reflected class.
     */
    public function ref(): \ReflectionClass
    {
        return $this->class;
    }

    /**
     * Returns the currently prepared instance.
     *
     * @return object|null Prepared instance, or `null` before construction.
     */
    public function getInstance(): ?object
    {
        return $this->instance;
    }

    /**
     * Uses an existing object as the instance target.
     *
     * @param object $instance Instance target.
     * @return object The supplied instance.
     */
    public function readyMade(object $instance): object
    {
        $this->instance = $instance;

        return $instance;
    }

    /**
     * Constructs the reflected class with positional arguments.
     *
     * @param mixed ...$args Constructor arguments.
     * @return static This reference with a new instance.
     */
    public function make(mixed ...$args): static
    {
        $this->instance = $this->class->newInstanceArgs($args);

        return $this;
    }

    /**
     * Constructs the reflected class with an argument array.
     *
     * @param array<int|string, mixed> $args Constructor arguments.
     * @return static This reference with a new instance.
     */
    public function makeArgs(array $args): static
    {
        $this->instance = $this->class->newInstanceArgs($args);

        return $this;
    }

    /**
     * Constructs the class without running its constructor.
     *
     * @return static This reference with a new instance.
     */
    public function noMake(): static
    {
        $this->instance = $this->class->newInstanceWithoutConstructor();

        return $this;
    }

    /**
     * Clears the class reflection cache.
     *
     * @return void
     */
    public static function resetCache(): void
    {
        self::$classes = [];
    }

    /**
     * Returns a cached class reflection object.
     *
     * @param class-string $className Class name.
     * @return \ReflectionClass<object> Reflected class.
     */
    private static function classFor(string $className): \ReflectionClass
    {
        return self::$classes[$className] ??= new \ReflectionClass($className);
    }
}

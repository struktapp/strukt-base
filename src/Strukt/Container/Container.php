<?php

declare(strict_types=1);

namespace Strukt\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * A small PSR-11 dependency container with constructor autowiring.
 *
 * ```php
 * $container->set(Cache::class, fn () => cache('app'));
 * $kernel = $container->get(Kernel::class);
 * $user = $container->make(UserController::class, ['request' => $request]);
 * ```
 *
 * Bindings can be shared (`set`), resolved per request (`factory`), or replaced
 * with an existing instance (`instance`). Anything without a binding is
 * autowired through its constructor, resolving constructor parameters from the
 * container or their type.
 *
 * The container also owns the process-wide shared instance returned by the
 * global `app()` and `container()` helpers.
 */
final class Container implements ContainerInterface
{
    /** @var self|null Process-wide shared container. */
    private static ?self $instance = null;

    /** @var array<string, callable(Container): mixed> Shared and factory bindings. */
    private array $factories = [];

    /** @var array<string, bool> Whether a binding is shared. */
    private array $shared = [];

    /** @var array<string, mixed> Prebuilt shared instances. */
    private array $instances = [];

    /** @var array<string, string> Alias to canonical identifier. */
    private array $aliases = [];

    /**
     * Returns the process-wide shared container, creating it on first use.
     *
     * @return self Shared container.
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Installs a process-wide shared container.
     *
     * @param self|null $container Container to install, or `null` to clear.
     * @return self|null Installed container.
     */
    public static function setInstance(?self $container): ?self
    {
        return self::$instance = $container;
    }

    /**
     * Registers a factory that resolves to a shared instance.
     *
     * @param string $id Identifier.
     * @param callable(Container): mixed $factory Factory callback.
     * @return static This container.
     */
    public function set(string $id, callable $factory): static
    {
        $this->factories[$id] = $factory;
        $this->shared[$id] = true;
        unset($this->instances[$id]);

        return $this;
    }

    /**
     * Registers a factory resolved on every request.
     *
     * @param string $id Identifier.
     * @param callable(Container): mixed $factory Factory callback.
     * @return static This container.
     */
    public function factory(string $id, callable $factory): static
    {
        $this->factories[$id] = $factory;
        $this->shared[$id] = false;
        unset($this->instances[$id]);

        return $this;
    }

    /**
     * Stores an already built instance.
     *
     * @param string $id Identifier.
     * @param mixed $value Instance to return on resolution.
     * @return static This container.
     */
    public function instance(string $id, mixed $value): static
    {
        $this->instances[$id] = $value;
        unset($this->factories[$id], $this->shared[$id]);

        return $this;
    }

    /**
     * Points one identifier at another.
     *
     * @param string $alias Alias identifier.
     * @param string $id Canonical identifier.
     * @return static This container.
     */
    public function alias(string $alias, string $id): static
    {
        $this->aliases[$alias] = $id;

        return $this;
    }

    /**
     * Resolves an identifier.
     *
     * @param string $id Identifier.
     * @return mixed Resolved value.
     *
     * @throws NotFoundException When the identifier is not registered.
     * @throws ContainerException When construction fails.
     */
    public function get(string $id): mixed
    {
        $id = $this->aliases[$id] ?? $id;

        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (array_key_exists($id, $this->factories)) {
            $value = ($this->factories[$id])($this);
            if (!empty($this->shared[$id])) {
                $this->instances[$id] = $value;
            }

            return $value;
        }

        if (class_exists($id)) {
            return $this->make($id);
        }

        throw new NotFoundException(sprintf('No entry was found for identifier [%s].', $id));
    }

    /**
     * Checks whether an identifier is resolvable.
     *
     * @param string $id Identifier.
     * @return bool True when resolvable.
     */
    public function has(string $id): bool
    {
        $id = $this->aliases[$id] ?? $id;

        return isset($this->instances[$id])
            || isset($this->factories[$id])
            || class_exists($id);
    }

    /**
     * Checks whether an identifier carries an explicit binding.
     *
     * Unlike {@see has()}, this reports `false` for a class that would merely
     * be autowired, so callers can tell a registered service apart from one
     * that only happens to be instantiable.
     *
     * @param string $id Identifier.
     * @return bool True when explicitly bound.
     */
    public function bound(string $id): bool
    {
        $id = $this->aliases[$id] ?? $id;

        return isset($this->instances[$id]) || isset($this->factories[$id]);
    }

    /**
     * Autowires a class through its constructor.
     *
     * @param string $class Class to build.
     * @param array<string, mixed> $parameters Named constructor arguments to use directly.
     * @return object Built instance.
     *
     * @throws ContainerException When the class is not instantiable or an argument cannot be resolved.
     */
    public function make(string $class, array $parameters = []): object
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $error) {
            throw new ContainerException(sprintf('Class [%s] could not be reflected: %s', $class, $error->getMessage()), 0, $error);
        }

        if (!$reflection->isInstantiable()) {
            throw new ContainerException(sprintf('Class [%s] is not instantiable.', $class));
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            try {
                $arguments[] = $this->argument($parameter, $parameters);
            } catch (NotFoundExceptionInterface $error) {
                throw new ContainerException(
                    sprintf('Cannot resolve parameter [$%s] of [%s].', $parameter->getName(), $class),
                    0,
                    $error,
                );
            }
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Resolves one constructor parameter.
     *
     * @param ReflectionParameter $parameter Parameter to resolve.
     * @param array<string, mixed> $parameters Named arguments.
     * @return mixed Dimension value.
     *
     * @throws NotFoundException When the parameter cannot be resolved.
     */
    private function argument(ReflectionParameter $parameter, array $parameters): mixed
    {
        $name = $parameter->getName();
        if (array_key_exists($name, $parameters)) {
            return $parameters[$name];
        }

        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this->get($type->getName());
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new NotFoundException(sprintf('No binding is available for [%s].', $name));
    }
}

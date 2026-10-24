Strukt Base
===========

[![Latest Stable Version](https://poser.pugx.org/strukt/base/v)](https://packagist.org/packages/strukt/base)
[![Total Downloads](https://poser.pugx.org/strukt/base/downloads)](https://packagist.org/packages/strukt/base)
[![License](https://poser.pugx.org/strukt/base/license)](https://packagist.org/packages/strukt/base)

Typed runtime foundations for Strukt packages. Base provides process-local
aliases and environment values, cached reflection wrappers, helper
registration, a small runtime exception type, a PSR-11 dependency
container exposed through app(), and Symfony-backed dd()/dump() debugging
helpers.

Requirements
------------

- PHP 8.2 or newer
- psr/container ^1.1 || ^2.0
- symfony/var-dumper ^7.0 || ^8.0

Installation
------------

```sh
composer require strukt/base
```

Composer loads src/helpers.php automatically. The package therefore exposes
its global helpers after the normal Composer autoloader has been required:

```php
require __DIR__ . '/vendor/autoload.php';

alias('user', App\User::class);
env('APP_ENV', 'production');

$user = ref(App\User::class)->makeArgs(['Ada'])->getInstance();
```

Global helpers
--------------

The helpers are conditionally defined so multiple Strukt packages can share a
process without redeclaring the same function. The base helper registry keeps
the names that were successfully installed.

### helper()

```php
helper(?string $package = null): Strukt\Helper|array|null
```

Reads the helper registry or selects a package for subsequent registrations.
Passing null, packages, pkg, or pkgs lists package names. Passing a package
name returns its registered helper names when the package is loaded; an
unknown package is selected and initialized.

```php
helper();          // ['base', ...]
helper('base');    // ['helper_add', 'is_map', ...]
helper('event');   // selects the event package when it is loaded
```

### helper_add()

```php
helper_add(string $functionName): bool
```

Registers a helper name for the package selected by helper(). It returns true
only when the name is available to define. Package helper files use this check
before declaring each global function.

### is_map()

```php
is_map(array $value): bool
```

Returns true for a non-empty associative array and false for an empty or
list-shaped array.

### alias()

```php
alias(?string $alias = null, ?string $longName = null): array|string|null
```

Registers, reads, or lists aliases:

```php
alias('user', App\User::class); // registers and returns the target
alias('user');                  // returns App\User::class
alias();                        // returns alias => target pairs
alias('user*');                 // returns matching alias => target pairs
```

Registering an existing alias throws LogicException. Empty names throw
InvalidArgumentException.

### env()

```php
env(string $key, string|int|bool|null $value = null): string|int|bool
```

Stores a process-local value when value is supplied, then returns the value.
Missing keys throw Strukt\Raise.

```php
env('APP_ENV', 'production');
$environment = env('APP_ENV');
```

### ref()

```php
ref(string|object|callable $value): mixed
```

Creates a Ref wrapper for a class name or object, or a FunctionReference for a
callable. It returns null when a string is not a class and the value is not
callable.

### raise()

```php
raise(string $message, int $code = 500): never
```

Throws Strukt\Raise immediately and records the message in the process-local
error list.

### timezone()

```php
timezone(?string $locale = null): string|false
```

Returns the configured PHP timezone. When the current configuration is UTC
and a locale is supplied, it changes the default timezone and returns that
locale. Without a locale it only reads the current value.

### negate() and notnull()

```php
negate(bool $value): bool
notnull(mixed $value): bool
```

negate() inverts a boolean. notnull() tests for strict non-nullness.

### dd() and dump()

`dd()` and `dump()` come from the symfony/var-dumper dependency and use
Symfony's VarDumper for their output; `dd()` dumps each value and exits with
status 1.

```php
dd(mixed ...$values): never
dump(mixed ...$values): mixed
```

symfony/var-dumper declares these globals first, so base normally defers to
them. Base also declares a guarded `dd()` fallback (using the same VarDumper)
that only takes effect when symfony's functions file was not loaded — for
example under PsySH, whose bundled autoloader deduplicates the shared file
identifier and skips the project's `dump.php`.

### app()

```php
app(?string $abstract = null, array $parameters = []): mixed
```

Resolves against the shared dependency container, or returns the container
itself. With no argument it returns the process-wide container; with an
identifier it resolves a binding or autowires the class; with parameters it
builds the class using the named constructor overrides.

```php
app();                                    // the shared container
app(Cache::class);                        // binding or autowired class
app(Controller::class, ['id' => 7]);      // built with overrides
```

Helper inventory
---------------

The base package exposes these global helper functions:

```text
helper, helper_add, is_map, alias, app, env, ref, raise, timezone,
negate, notnull
```

`dd()` and `dump()` are also available (via symfony/var-dumper). Because
symfony declares them first, they are normally not registered in base's helper
registry; `dd` is registered only when base's guarded fallback is the one that
defines it.

helper() is the registry bootstrap function, so the registry's base list starts
with helper_add and then records the remaining conditionally-defined helpers.

The class APIs below are the typed equivalents behind those helpers.

Alias API
---------

Strukt\Alias stores short names for classes, commands, and events.

```php
Alias::set(string $alias, string $longName): string
Alias::get(string $alias): ?string
Alias::exists(string $alias): bool
Alias::ls(?string $startsWith = null): array
Alias::reset(): void
```

set() registers an alias once and throws when the alias or target is empty or
already registered. ls() returns alias names, optionally restricted to a
prefix. reset() clears the process-local store.

Environment API
---------------

Strukt\Env stores typed values in memory; it does not mutate PHP's process
environment.

```php
Env::withFile(string $path = '.env'): void
Env::has(string $key): bool
Env::get(string $key): string|int|bool
Env::set(string $key, string|int|bool $value): void
Env::reset(): void
```

withFile() accepts blank lines, # and // comments, an optional export prefix,
quoted values, and values containing =. The strings true and false are loaded
as booleans. A missing or unreadable file throws InvalidArgumentException;
get() throws Strukt\Raise for an unknown key.

Reflection API
--------------

Strukt\Ref caches ReflectionClass objects for the lifetime of the process.
Use resetCache() when a long-running process needs to discard that cache.

```php
Ref::func(callable $function): FunctionReference
Ref::createFrom(object $instance): Ref
Ref::create(string $className): Ref
Ref::resetCache(): void
```

Class and instance references expose:

```php
$reference->method(string $name): MethodReference
$reference->props(): array
$reference->prop(string $name): PropertyReference
$reference->ref(): ReflectionClass
$reference->getInstance(): ?object
$reference->readyMade(object $instance): object
$reference->make(mixed ...$args): Ref
$reference->makeArgs(array $args): Ref
$reference->noMake(): Ref
```

make() and makeArgs() construct an object; noMake() creates one without
running its constructor. Instance methods and properties require an instance,
which can be supplied with createFrom(), readyMade(), or a construction
method.

### FunctionReference

```php
$reference->ref(): ReflectionFunction
$reference->invoke(mixed ...$args): mixed
```

### MethodReference

```php
$reference->ref(): ReflectionMethod
$reference->getClosure(): Closure
$reference->invoke(mixed ...$args): mixed
```

### PropertyReference

```php
$reference->ref(): ReflectionProperty
$reference->set(mixed $value): void
$reference->get(): mixed
```

These wrappers also work with non-public members through PHP reflection.

Helper registry API
-------------------

Strukt\Helper tracks helper names per package:

```php
Helper::__construct(string $package)
Helper::create(string $package): Helper
Helper::singleton(): ?Helper
$registry->register(string $functionName): bool
$registry->get(string $package): ?array
$registry->listPackages(): array
Helper::reset(): void
```

create() selects the shared registry and package. register() is intended for
conditional helper declarations and refuses names that already exist as global
functions.

Raise API
---------

Strukt\Raise is the runtime exception used by the helpers.

```php
new Raise(string $message, int $code = 500)
Raise::errors(): array
Raise::reset(): void
```

Each exception records its message in Raise::errors(). reset() clears the
process-local error history.

Container API
-------------

Strukt\Container\Container is a small PSR-11 container with constructor
autowiring. It owns the process-wide instance returned by app() and by the
framework container() helper.

```php
Container::getInstance(): Container
Container::setInstance(?Container $container): ?Container

$container->set(string $id, callable $factory): static      // shared
$container->factory(string $id, callable $factory): static  // per resolve
$container->instance(string $id, mixed $value): static      // prebuilt
$container->alias(string $alias, string $id): static        // pointer
$container->get(string $id): mixed
$container->has(string $id): bool
$container->make(string $class, array $parameters = []): object
```

get() returns a binding or autowires the class; make() reflects the
constructor and resolves each parameter from a named override, then the
container, then a default. Missing identifiers throw
Strukt\Container\NotFoundException; failed construction throws
Strukt\Container\ContainerException (both PSR-11 exceptions).

```php
use Strukt\Container\Container;

$container = new Container();
$container->set(Mailer::class, fn (Container $c) => new Mailer($c->get(Transport::class)));

$mailer = $container->get(Mailer::class);              // shared
$user = $container->make(User::class, ['id' => 7]);    // autowired + override
```

The container requires psr/container, which supplies the two PSR-11
interfaces it implements.

Performance notes
-----------------

- Class reflection objects are cached by Ref.
- Helpers avoid repeated registration and are only defined when their names
  are available.
- Environment values and aliases use in-memory arrays for constant-time
  lookup.
- FunctionReference and the method/property wrappers retain their native
  reflection object instead of rebuilding it for every call.

Testing
-------

```sh
composer test
composer lint
```

Examples
--------

Runnable container walk-throughs live in `examples/container/`, built around a
small order-service domain (repository, payment gateway, notifier, and an
`OrderService` that depends only on their interfaces).

```sh
php examples/container/06-order-checkout.php
```

See `examples/README.md` for the directory index and
`examples/container/README.md` for the full list and the container mental
model.


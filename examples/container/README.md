# Strukt Base — Container Examples

Runnable scripts that show how `Strukt\Container\Container` and its `app()`
helper behave, told through a small order-service domain. Each file loads the
package autoloader and prints its results to stdout.

## Run from the package root

```bash
# one example
php examples/container/01-bindings.php

# every example, in order
for f in examples/container/[0-9][0-9]-*.php; do
  echo "== $f =="
  php "$f"
done
```

## Contents

| File | Shows |
| --- | --- |
| `01-bindings.php` | `instance()`, `set()` (shared), and `factory()` (transient); `has()`. |
| `02-autowiring.php` | `get()` reflecting a constructor and resolving typed dependencies. |
| `03-interface-bindings.php` | Binding an interface to a concrete class and swapping it. |
| `04-named-arguments.php` | `make()`/`app()` with named overrides, defaults, and the failure mode. |
| `05-app-and-psr11.php` | The shared `app()` container, PSR-11 behaviour, `alias()`, `setInstance()`. |
| `06-order-checkout.php` | A full checkout wired end to end with `new Container()`, then re-wired to a new gateway. |
| `07-the-app-helper.php` | The same checkout (repository, gateway, and notifier) wired entirely through the `app()` helper instead of a container variable. |
| `08-fallback-default.php` | A `factory()` selector that falls back to a default (`instance()`) gateway when the live providers are unavailable, callable as `app('gateway')->pay(...)`. |

## The mental model

- **`set()` is shared.** The factory runs once; every `get()` returns that same
  object. Use it for services that hold state or connections.
- **`factory()` is transient.** The factory runs on every `get()`, returning a
  fresh object. Use it for short-lived objects.
- **`instance()` is prebuilt.** You hand over an object that is already
  constructed.
- **`get()` autowires.** If an identifier has no binding but names a class, the
  container reflects its constructor and resolves each parameter — a named
  override first, then the container, then the parameter default. Interfaces are
  never autowired: bind them to a concrete class.
- **`app()` is the process-wide container.** `app()` returns it, `app($id)`
  resolves an entry, and `app($id, $params)` builds with overrides.
- **Friendly keys are explicit.** `app('gateway')` only works because something
  registered that key (`set('gateway', ...)` or
  `alias('gateway', PaymentGateway::class)`); the container never infers it from
  the `gateway.*` binding names.

See `../../README.md` for the full helper and container API reference.

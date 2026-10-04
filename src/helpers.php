<?php

use Strukt\Alias;
use Strukt\Env;
use Strukt\Helper;
use Strukt\Raise;
use Strukt\Ref;

/** Base package helper functions. */

if (!function_exists('helper')) {
    /**
     * Reads or selects the package helper registry.
     *
     * @param string|null $package Package name or `packages` to list packages.
     * @return Helper|array|null Registry, package names, or helper names.
     */
    function helper(?string $package = null): Helper|array|null
    {
        $listRequest = $package === null || in_array($package, ['packages', 'pkg', 'pkgs'], true);
        $registry = Helper::singleton();

        if ($registry === null) {
            if ($listRequest) {
                if ($package === null) {
                    Helper::create('base');
                    return Helper::singleton()?->listPackages() ?? [];
                }

                return [];
            }

            return Helper::create($package ?? 'base');
        }

        if ($listRequest) {
            return $registry->listPackages();
        }

        $registered = $registry->get($package);
        if ($registered !== null) {
            $registry->pkg_name = trim($package);
            return $registered;
        }

        return Helper::create($package);
    }
}

if (!function_exists('helper_add')) {
    /**
     * Registers the next helper name for the selected package.
     *
     * @param string $functionName Function name.
     * @return bool True when the function can be defined.
     */
    function helper_add(string $functionName): bool
    {
        $registry = Helper::singleton() ?? Helper::create('base');

        return $registry->register($functionName);
    }
}

helper('base');

if (!function_exists('is_map') && helper_add('is_map')) {
    /**
     * Checks whether an array is a non-empty associative map.
     *
     * @param array<int|string, mixed> $value Array to inspect.
     * @return bool True for a non-empty non-list array.
     */
    function is_map(array $value): bool
    {
        return $value !== [] && !array_is_list($value);
    }
}

if (!function_exists('alias') && helper_add('alias')) {
    /**
     * Registers, reads, or lists aliases.
     *
     * @param string|null $alias Alias name or wildcard prefix.
     * @param string|null $longName Target name when registering.
     * @return array<string, string>|list<string>|string|null Alias map, names, or target.
     */
    function alias(?string $alias = null, ?string $longName = null): array|string|null
    {
        if ($alias === null) {
            $result = [];
            foreach (Alias::ls() as $name) {
                $result[$name] = Alias::get($name);
            }

            return $result;
        }

        if ($longName !== null) {
            return Alias::set($alias, $longName);
        }

        if (str_ends_with($alias, '*')) {
            $prefix = rtrim($alias, '*');
            $result = [];
            foreach (Alias::ls($prefix) as $name) {
                $result[$name] = Alias::get($name);
            }

            return $result;
        }

        return Alias::get($alias);
    }
}

if (!function_exists('env') && helper_add('env')) {
    /**
     * Reads or stores an environment value.
     *
     * @param string $key Environment key.
     * @param string|int|bool|null $value Optional value to store.
     * @return string|int|bool Stored or loaded value.
     */
    function env(string $key, string|int|bool|null $value = null): string|int|bool
    {
        if ($value !== null) {
            Env::set($key, $value);
        }

        return Env::get($key);
    }
}

if (!function_exists('ref') && helper_add('ref')) {
    /**
     * Creates a reflection wrapper for a class, object, or callable.
     *
     * @param string|object|callable $value Reflection target.
     * @return mixed Reflection wrapper, or `null` for an invalid target.
     */
    function ref(string|object|callable $value): mixed
    {
        if (is_string($value) && class_exists($value)) {
            return Ref::create($value);
        }

        if (is_object($value) && !$value instanceof \Closure) {
            return Ref::createFrom($value);
        }

        if (is_callable($value)) {
            return Ref::func($value);
        }

        return null;
    }
}

if (!function_exists('raise') && helper_add('raise')) {
    /**
     * Throws a Strukt runtime exception.
     *
     * @param string $message Error message.
     * @param int $code Exception code.
     * @return never Never returns.
     * @throws Raise Always.
     */
    function raise(string $message, int $code = 500): never
    {
        throw new Raise($message, $code);
    }
}

if (!function_exists('timezone') && helper_add('timezone')) {
    /**
     * Reads the configured timezone and optionally changes it from UTC.
     *
     * @param string|null $locale New timezone when the current value is UTC.
     * @return string|false Current or selected timezone.
     */
    function timezone(?string $locale = null): string|false
    {
        $current = ini_get('date.timezone');
        if ($current === 'UTC' && $locale !== null) {
            date_default_timezone_set($locale);
            return $locale;
        }

        return $locale ?? $current;
    }
}

if (!function_exists('negate') && helper_add('negate')) {
    /**
     * Negates a boolean.
     *
     * @param bool $value Value to invert.
     * @return bool Inverted value.
     */
    function negate(bool $value): bool
    {
        return !$value;
    }
}

if (!function_exists('notnull') && helper_add('notnull')) {
    /**
     * Checks whether a value is not null.
     *
     * @param mixed $value Value to inspect.
     * @return bool True when the value is not null.
     */
    function notnull(mixed $value): bool
    {
        return $value !== null;
    }
}

if (!function_exists('dd') && helper_add('dd')) {
    /**
     * Dumps values and terminates execution.
     *
     * @param mixed ...$values Values to dump.
     * @return never Never returns.
     */
    function dd(mixed ...$values): never
    {
        foreach ($values as $value) {
            var_dump($value);
        }

        exit(1);
    }
}

<?php

namespace Strukt;

/** Stores short names for commands, events, and classes. */
final class Alias
{
    /** @var array<string, string> Registered aliases. */
    private static array $aliases = [];

    /**
     * Registers an alias once.
     *
     * @param string $alias Short name to register.
     * @param string $longName Target name represented by the alias.
     * @return string The registered target name.
     * @throws \InvalidArgumentException When either name is empty.
     * @throws \LogicException When the alias already exists.
     */
    public static function set(string $alias, string $longName): string
    {
        $alias = trim($alias);
        $longName = trim($longName);

        if ($alias === '' || $longName === '') {
            throw new \InvalidArgumentException('Alias and target names must not be empty.');
        }

        if (array_key_exists($alias, self::$aliases)) {
            throw new \LogicException(sprintf('Alias [%s] already exists.', $alias));
        }

        self::$aliases[$alias] = $longName;

        return $longName;
    }

    /**
     * Reads a registered alias.
     *
     * @param string $alias Alias name.
     * @return string|null Target name, or `null` when absent.
     */
    public static function get(string $alias): ?string
    {
        return self::$aliases[$alias] ?? null;
    }

    /**
     * Checks whether an alias exists.
     *
     * @param string $alias Alias name.
     * @return bool True when the alias is registered.
     */
    public static function exists(string $alias): bool
    {
        return array_key_exists($alias, self::$aliases);
    }

    /**
     * Lists aliases, optionally restricted to a prefix.
     *
     * @param string|null $startsWith Prefix to match.
     * @return list<string> Matching alias names.
     */
    public static function ls(?string $startsWith = null): array
    {
        if ($startsWith === null || $startsWith === '') {
            return array_keys(self::$aliases);
        }

        $aliases = [];
        foreach (self::$aliases as $alias => $_target) {
            if (str_starts_with($alias, $startsWith)) {
                $aliases[] = $alias;
            }
        }

        return $aliases;
    }

    /**
     * Clears all aliases.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$aliases = [];
    }
}

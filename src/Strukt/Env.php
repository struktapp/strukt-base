<?php

namespace Strukt;

/** Loads and stores small, process-local environment values. */
final class Env
{
    /** @var array<string, string|int|bool> Loaded environment values. */
    private static array $values = [];

    /**
     * Loads key/value pairs from an environment file.
     *
     * Empty lines, `#` comments, `//` comments, and an optional `export`
     * prefix are ignored. Values may be wrapped in matching quotes.
     *
     * @param string $path Environment file path.
     * @return void
     * @throws \InvalidArgumentException When the file does not exist.
     */
    public static function withFile(string $path = '.env'): void
    {
        if (class_exists(Phar::class) && Phar::isPhar()) {
            $path = Phar::adapt($path);
        }

        if (!is_file($path)) {
            throw new \InvalidArgumentException(sprintf('Environment file [%s] was not found.', $path));
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new \InvalidArgumentException(sprintf('Environment file [%s] could not be read.', $path));
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '//')) {
                continue;
            }

            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            if ($key === '') {
                continue;
            }

            $value = trim($value);
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            $parsed = match (strtolower($value)) {
                'true' => true,
                'false' => false,
                default => $value,
            };

            self::set($key, $parsed);
        }
    }

    /**
     * Checks whether a key has been loaded.
     *
     * @param string $key Environment key.
     * @return bool True when the key exists.
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$values);
    }

    /**
     * Reads an environment value.
     *
     * @param string $key Environment key.
     * @return string|int|bool Value associated with the key.
     * @throws Raise When the key is not loaded.
     */
    public static function get(string $key): string|int|bool
    {
        if (!self::has($key)) {
            throw new Raise(sprintf("Couldn't get .env[%s].", $key));
        }

        return self::$values[$key];
    }

    /**
     * Stores a value without touching PHP's process environment.
     *
     * @param string $key Environment key.
     * @param string|int|bool $value Value to store.
     * @return void
     */
    public static function set(string $key, string|int|bool $value): void
    {
        self::$values[$key] = $value;
    }

    /**
     * Clears all loaded values.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$values = [];
    }
}

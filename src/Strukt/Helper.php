<?php

namespace Strukt;

/** Tracks helper names registered by each Strukt package. */
final class Helper
{
    /** @var array<string, list<string>> Package helper names. */
    public array $all = [];

    /** Current package receiving registrations. */
    public string $pkg_name;

    /** Shared helper registry. */
    public static ?self $app = null;

    /**
     * Creates a helper registry for a package.
     *
     * @param string $package Package name.
     */
    public function __construct(string $package)
    {
        $this->pkg_name = trim($package);
        $this->all['base'] = ['helper_add'];

        if ($this->pkg_name !== 'base' && $this->pkg_name !== '') {
            $this->all[$this->pkg_name] = [];
        }
    }

    /**
     * Creates or selects the shared registry.
     *
     * @param string $package Package to select.
     * @return static Shared registry.
     */
    public static function create(string $package): static
    {
        $package = trim($package);
        if (self::$app === null) {
            self::$app = new static($package === '' ? 'base' : $package);
        }

        self::$app->pkg_name = $package === '' ? 'base' : $package;
        self::$app->all[self::$app->pkg_name] ??= [];

        return self::$app;
    }

    /**
     * Returns the registry when initialized.
     *
     * @return static|null Shared registry, or `null` before initialization.
     */
    public static function singleton(): ?static
    {
        return self::$app;
    }

    /**
     * Records a helper name if it is not already defined.
     *
     * @param string $functionName Function name to register.
     * @return bool True when the name was recorded.
     */
    public function register(string $functionName): bool
    {
        $functionName = trim($functionName);
        if ($functionName === '' || function_exists($functionName)) {
            return false;
        }

        $this->all[$this->pkg_name] ??= [];
        if (!in_array($functionName, $this->all[$this->pkg_name], true)) {
            $this->all[$this->pkg_name][] = $functionName;
        }

        return true;
    }

    /**
     * Reads a package's registered helper names.
     *
     * @param string $package Package name.
     * @return list<string>|null Helper names, or `null` when absent.
     */
    public function get(string $package): ?array
    {
        return $this->all[trim($package)] ?? null;
    }

    /**
     * Lists all registered package names.
     *
     * @return list<string> Package names.
     */
    public function listPackages(): array
    {
        return array_keys($this->all);
    }

    /**
     * Resets the shared registry.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$app = null;
    }
}

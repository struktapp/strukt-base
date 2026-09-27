<?php

namespace Strukt;

/** Runtime exception used by Strukt helper functions. */
class Raise extends \RuntimeException
{
    /** @var list<string> Messages raised during this process. */
    protected static array $errors = [];

    /**
     * Creates and records a runtime error.
     *
     * @param string $message Error message.
     * @param int $code Exception code.
     */
    public function __construct(string $message, int $code = 500)
    {
        self::$errors[] = $message;
        parent::__construct($message, $code);
    }

    /**
     * Returns recorded error messages.
     *
     * @return list<string> Raised messages.
     */
    public static function errors(): array
    {
        return self::$errors;
    }

    /**
     * Clears recorded error messages.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$errors = [];
    }
}

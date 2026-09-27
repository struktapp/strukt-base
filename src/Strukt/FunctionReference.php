<?php

namespace Strukt;

/** Invokes a cached reflection description of a callable function. */
final class FunctionReference
{
    /**
     * @param \ReflectionFunction $reflection Reflected callable.
     */
    public function __construct(private \ReflectionFunction $reflection)
    {
    }

    /**
     * Returns the underlying reflection object.
     *
     * @return \ReflectionFunction Reflected function.
     */
    public function getRef(): \ReflectionFunction
    {
        return $this->reflection;
    }

    /**
     * Invokes the function with positional arguments.
     *
     * @param mixed ...$args Arguments for the callable.
     * @return mixed Callable result.
     */
    public function invoke(mixed ...$args): mixed
    {
        return $this->reflection->invokeArgs($args);
    }
}

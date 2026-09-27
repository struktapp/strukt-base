<?php

namespace Strukt;

/** Invokes and inspects a reflected object method. */
final class MethodReference
{
    /**
     * @param \ReflectionMethod $reflection Reflected method.
     * @param object|null $instance Object target, or `null` for a static method.
     */
    public function __construct(
        private \ReflectionMethod $reflection,
        private ?object $instance,
    ) {
    }

    /**
     * Returns the underlying reflection object.
     *
     * @return \ReflectionMethod Reflected method.
     */
    public function getRef(): \ReflectionMethod
    {
        return $this->reflection;
    }

    /**
     * Creates a closure for the method.
     *
     * @return \Closure Bound method closure.
     */
    public function getClosure(): \Closure
    {
        return $this->reflection->getClosure($this->instance);
    }

    /**
     * Invokes the method.
     *
     * @param mixed ...$args Method arguments.
     * @return mixed Method result.
     */
    public function invoke(mixed ...$args): mixed
    {
        return $this->reflection->invokeArgs($this->instance, $args);
    }
}

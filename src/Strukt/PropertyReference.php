<?php

namespace Strukt;

/** Reads and writes a reflected object or static property. */
final class PropertyReference
{
    /**
     * @param \ReflectionProperty $reflection Reflected property.
     * @param object|null $instance Object target, or `null` for a static property.
     */
    public function __construct(
        private \ReflectionProperty $reflection,
        private ?object $instance,
    ) {
    }

    /**
     * Returns the underlying reflection object.
     *
     * @return \ReflectionProperty Reflected property.
     */
    public function ref(): \ReflectionProperty
    {
        return $this->reflection;
    }

    /**
     * Assigns a property value.
     *
     * @param mixed $value Value to assign.
     * @return void
     */
    public function set(mixed $value): void
    {
        if ($this->instance === null) {
            $this->reflection->setValue($value);
            return;
        }

        $this->reflection->setValue($this->instance, $value);
    }

    /**
     * Reads a property value.
     *
     * @return mixed Property value.
     */
    public function get(): mixed
    {
        return $this->instance === null
            ? $this->reflection->getValue()
            : $this->reflection->getValue($this->instance);
    }
}

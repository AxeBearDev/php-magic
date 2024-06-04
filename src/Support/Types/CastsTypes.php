<?php

namespace AxeBear\Magic\Support\Types;

interface CastsTypes
{
    /**
     * Does this caster support the provided type?
     */
    public static function supports(string $type): bool;

    /**
     * Converts the value to the specified type.
     *
     * @param  mixed  $default
     *
     * @throws \InvalidArgumentException|\OutOfRangeException
     */
    public static function cast(string $type, mixed $value): mixed;
}

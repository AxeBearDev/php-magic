<?php

namespace AxeBear\Magic\Support\Types;

class ClassCaster implements CastsTypes
{
    public static function supports(string $type): bool
    {
        return class_exists($type);
    }

    public static function cast(string $type, mixed $value): mixed
    {
        if (! class_exists($type)) {
            throw new \InvalidArgumentException("Class does not exist: {$type}");
        }

        if (is_a($value, $type)) {
            return $value;
        }

        return new $type($value);
    }
}

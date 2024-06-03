<?php

namespace AxeBear\Magic\Support\Types;

class ClassConverter implements ConvertsType
{
    public static function supports(string $type): bool
    {
        return class_exists($type);
    }

    public static function convert(string $type, mixed $value): mixed
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

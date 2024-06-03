<?php

namespace AxeBear\Magic\Support\Types;

class MixedConverter implements ConvertsType
{
    public static function supports(string $type): bool
    {
        return $type === 'mixed';
    }

    public static function convert(string $type, mixed $value): mixed
    {
        return $value;
    }
}

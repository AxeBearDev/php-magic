<?php

namespace AxeBear\Magic\Support\Types;

class MixedCaster implements CastsTypes
{
    public static function supports(string $type): bool
    {
        return $type === 'mixed';
    }

    public static function cast(string $type, mixed $value): mixed
    {
        return $value;
    }
}

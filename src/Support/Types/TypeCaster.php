<?php

namespace AxeBear\Magic\Support\Types;

use InvalidArgumentException;

/**
 * Converts PHPStan doc types to PHP types.
 *
 * @see https://phpstan.org/writing-php-code/phpdoc-types
 */
class TypeCaster implements CastsTypes
{
    /**
     * The registered casters. Order matters. The first supported caster will be used.
     */
    protected static array $casters = [
        MixedCaster::class,
        BuiltinCaster::class,
        IntRangeCaster::class,
        TypedArrayCaster::class,
        ClassCaster::class,
    ];

    public static function append(string $caster): void
    {
        self::$casters[] = $caster;
    }

    public static function prepend(string $caster): void
    {
        array_unshift(self::$casters, $caster);
    }

    protected static function casterForType(string $type): ?string
    {
        foreach (self::$casters as $caster) {
            if ($caster::supports($type)) {
                return $caster;
            }
        }

        return null;
    }

    public static function supports(string $type): bool
    {
        return (bool) self::casterForType($type);
    }

    public static function cast(string $type, mixed $value, mixed $default = null): mixed
    {
        if ($caster = self::casterForType($type)) {
            return $caster::cast($type, $value, $default);
        }

        throw new InvalidArgumentException('Unsupported type: '.$type);
    }
}

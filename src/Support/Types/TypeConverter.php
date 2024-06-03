<?php

namespace AxeBear\Magic\Support\Types;

use InvalidArgumentException;

/**
 * Converts PHPStan doc types to PHP types.
 *
 * @see https://phpstan.org/writing-php-code/phpdoc-types
 */
class TypeConverter implements ConvertsType
{
    protected static array $converters = [
        BuiltinConverter::class,
        ClassConverter::class,
        IntRangeConverter::class,
        TypedArrayConverter::class,
        MixedConverter::class,
    ];

    public static function append(string $converter): void
    {
        self::$converters[] = $converter;
    }

    public static function prepend(string $converter): void
    {
        array_unshift(self::$converters, $converter);
    }

    public static function supports(string $type): bool
    {
        foreach (self::$converters as $converter) {
            if ($converter::supports($type)) {
                return true;
            }
        }

        return false;
    }

    public static function convert(string $type, mixed $value, mixed $default = null): mixed
    {
        foreach (self::$converters as $converter) {
            if ($converter::supports($type)) {
                return $converter::convert($type, $value, $default);
            }
        }

        throw new InvalidArgumentException('Unsupported type: '.$type);
    }
}

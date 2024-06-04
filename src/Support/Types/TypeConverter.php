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
    /**
     * The registered converters. Order matters. The first supported converter will be used.
     */
    protected static array $converters = [
        MixedConverter::class,
        BuiltinConverter::class,
        IntRangeConverter::class,
        TypedArrayConverter::class,
        ClassConverter::class,
    ];

    public static function append(string $converter): void
    {
        self::$converters[] = $converter;
    }

    public static function prepend(string $converter): void
    {
        array_unshift(self::$converters, $converter);
    }

    protected static function converterForType(string $type): ?string
    {
        foreach (self::$converters as $converter) {
            if ($converter::supports($type)) {
                return $converter;
            }
        }

        return null;
    }

    public static function supports(string $type): bool
    {
        return (bool) self::converterForType($type);
    }

    public static function convert(string $type, mixed $value, mixed $default = null): mixed
    {
        if ($converter = self::converterForType($type)) {
            return $converter::convert($type, $value, $default);
        }

        throw new InvalidArgumentException('Unsupported type: '.$type);
    }
}

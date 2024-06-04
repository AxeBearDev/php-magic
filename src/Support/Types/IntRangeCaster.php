<?php

namespace AxeBear\Magic\Support\Types;

/**
 * Converts a value to an int and validates it against a range.
 *
 * positive-int
 * negative-int
 * non-positive-int
 * non-negative-int
 * non-zero-int
 * int<0, 100>
 * int<min, 100>
 * int<50, max>
 */
class IntRangeCaster implements CastsTypes
{
    protected static string $rangePattern = '/^int<(\d+|min), (\d+|max)>$/';

    public static function supports(string $type): bool
    {
        return in_array($type, [
            'non-zero-int',
            'positive-int',
            'non-negative-int',
            'negative-int',
            'non-positive-int',
        ]) || preg_match(self::$rangePattern, $type);
    }

    public static function cast(string $type, mixed $value): mixed
    {
        $int = (int) $value;
        $min = PHP_INT_MIN;

        $test = function ($passes) use ($int, $type) {
            if ($passes) {
                return $int;
            }

            throw new \OutOfRangeException("Value {$int} is outside the valid int range for type {$type}.");
        };

        if ($type === 'non-zero-int') {
            return $test($int !== 0);
        }

        if ($type === 'positive-int') {
            return $test($int > 0);
        }

        if ($type === 'non-negative-int') {
            return $test($int >= 0);
        }

        if ($type === 'negative-int') {
            return $test($int < 0);
        }

        if ($type === 'non-positive-int') {
            return $test($int <= 0);
        }

        if (preg_match(self::$rangePattern, $type, $matches)) {
            $min = $matches[1] === 'min' ? PHP_INT_MIN : (int) $matches[1];
            $max = $matches[2] === 'max' ? PHP_INT_MAX : (int) $matches[2];

            return $test($int >= $min && $int <= $max);
        }

        throw new \InvalidArgumentException("Unsupported type: '{$type}'");
    }
}

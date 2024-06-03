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
class IntRangeConverter implements ConvertsType
{
    public static function supports(string $type): bool
    {
        return in_array($type, [
            'non-zero-int',
            'positive-int',
            'non-negative-int',
            'negative-int',
            'non-positive-int',
        ]) || preg_match('/^int<(\d+|min), (\d+|max)>$/', $type);
    }

    public static function convert(string $type, mixed $value): mixed
    {
        $int = (int) $value;

        $test = function ($passes) use ($int, $type) {
            if ($passes($int)) {
                return $int;
            }

            throw new \OutOfRangeException("Value {$int} is outside the valid int range for type {$type}.");
        };

        // Check specific ranges first.
        if (preg_match('/^int<(\d+), (\d+)>$/', $type, $matches)) {
            $min = $matches[1] === 'min' ? PHP_INT_MIN : (int) $matches[1];
            $max = $matches[2] === 'max' ? PHP_INT_MAX : (int) $matches[2];

            return $test($int >= $min && $int <= $max);
        }

        if ($type === 'non-zero-int') {
            return $test($int !== 0);
        }

        if ($type === 'positive-int' || $type === 'non-negative-int') {
            return $test($int > 0);
        }

        if ($type === 'negative-int' || $type === 'non-positive-int') {
            return $test($int < 0);
        }

        throw new \InvalidArgumentException("Unsupported type: {$type}");
    }
}

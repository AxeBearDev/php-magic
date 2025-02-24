<?php

namespace AxeBear\Magic\Support;

use Closure;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Provides static methods for generating chainable array functions.
 * Each method returns a closure that can be used in a Chain.
 * Generally, all methods will:
 * - Accept an array as the only argument
 * - Preserve keys (use Arr::values() to reset keys)
 * - Return a new array (unless otherwise specified)
 */
class Arr
{
    /**
     * Executes a callable on each item in the array and returns the result.
     */
    public static function map(callable $fn): Closure
    {
        return fn ($array) => array_map($fn, $array);
    }

    /**
     * Creates a group of items grouped by the result of the $getKey callable.
     *
     * @param fn ($value, $key) => mixed $getValue
     */
    public static function groupBy(callable $getKey): Closure
    {
        return function ($array) use ($getKey) {
            $grouped = [];

            foreach ($array as $key => $value) {
                $groupKey = $getKey($value, $key);
                $grouped[$groupKey][] = $value;
            }

            return $grouped;
        };
    }

    /**
     * Filters the array to only include items that pass the specified test
     */
    public static function where(?callable $test = null): Closure
    {
        return function ($array) use ($test) {
            $found = [];
            $test ??= fn ($value, $key) => (bool) $value;

            foreach ($array as $key => $value) {
                if ($test($value, $key)) {
                    $found[$key] = $value;
                }
            }

            return $found;
        };
    }

    /**
     * Filters the array to only include items that are truthy
     */
    public static function whereTruthy(): Closure
    {
        return static::where(fn ($value) => (bool) $value);
    }

    /**
     * Filters the array to only include items that are falsy
     */
    public static function whereFalsy(): Closure
    {
        return static::where(fn ($value) => ! $value);
    }

    /**
     * Flattens a multi-dimensional array
     */
    public static function flatten(): Closure
    {
        return fn ($array) => iterator_to_array(
            new RecursiveIteratorIterator(
                new RecursiveArrayIterator($array)
            ),
            false
        );
    }

    /**
     * Calls a callable on each item in the array
     */
    public static function each(callable $fn): Closure
    {
        return function ($array) use ($fn) {
            array_walk($array, $fn);

            return $array;
        };
    }

    /**
     * Gets the unique values from the array
     */
    public static function unique(?Closure $getValue = null): Closure
    {
        $getValue ??= fn ($value) => $value;

        return function ($array) use ($getValue) {
            $values = array_map($getValue, $array);
            $unique = array_unique($values);
            $keys = array_keys($unique);

            $result = array_intersect_key($array, array_flip($keys));

            return $result;
        };
    }

    /**
     * Returns the keys of the array
     */
    public static function keys(): Closure
    {
        return fn ($array) => array_keys($array);
    }

    /**
     * Returns the values of the array
     */
    public static function values(): Closure
    {
        return fn ($array) => array_values($array);
    }

    /**
     * Flips the keys and values of the array
     */
    public static function flip(): Closure
    {
        return fn ($array) => array_flip($array);
    }

    /**
     * Merges the array with other arrays
     */
    public static function merge(array ...$arrays): Closure
    {
        return fn ($original) => array_merge($original, ...$arrays);
    }

    /**
     * Intersects the array with other arrays
     */
    public static function intersect(array ...$arrays): Closure
    {
        return fn ($original) => array_intersect($original, ...$arrays);
    }

    /**
     * Returns the difference between the array the provided arrays
     */
    public static function diff(array ...$arrays): Closure
    {
        return fn ($original) => array_diff($original, ...$arrays);
    }

    /**
     * Splits the array into chunks of a specified size
     */
    public static function chunk(int $size, bool $preserveKeys = false): Closure
    {
        return fn ($array) => array_chunk($array, $size, $preserveKeys);
    }

    /**
     * Set the keys of the array to those provided
     */
    public static function setKeys(array $keys): Closure
    {
        return fn ($array) => array_combine($keys, $array);
    }

    /**
     * Returns the values from a given column and optional index.
     */
    public static function column(mixed $columnKey, mixed $indexKey = null): Closure
    {
        return fn ($array) => array_column($array, $columnKey, $indexKey);
    }

    /**
     * Sorts the array. If a callable is provided, it will use that to sort.
     * If true, it will sort ascending. If false, it will sort descending.
     */
    public static function sort(bool|callable $how = true): Closure
    {
        return function ($array) use ($how) {
            $copy = [...$array];
            if (is_callable($how)) {
                usort($copy, $how);
            } elseif ($how === false) {
                rsort($copy);
            } elseif ($how === true) {
                sort($copy);
            }

            return $copy;
        };
    }

    /**
     * Returns the count of the array
     */
    public static function count(int $mode = COUNT_NORMAL): Closure
    {
        return fn ($array) => count($array, $mode);
    }

    /**
     * Returns the first value. If a test is provided, it returns the first value that passes the test.
     */
    public static function first(
        ?Closure $test = null,
        mixed $default = null,
        ?bool $preserveKeys = false
    ): Closure {
        return function ($array) use ($test, $default, $preserveKeys) {
            $copy = [...$array];
            if (! $test) {
                $firstKey = array_key_first($copy);
                $firstValue = $copy[$firstKey] ?? $default;

                return $preserveKeys ? [$firstKey => $firstValue] : $firstValue;
            }

            foreach ($copy as $key => $item) {
                if ($test($item, $key)) {
                    return $preserveKeys ? [$key => $item] : $item;
                }
            }

            return $default;
        };
    }

    /**
     * Returns true if none of the array items pass the callable condition.
     */
    public static function none(callable $test): Closure
    {
        return static::all(fn ($item, $key) => ! $test($item, $key));
    }

    /**
     * Returns true if all array items pass the callable condition.
     */
    public static function all(callable $test): Closure
    {
        return function ($array) use ($test) {
            foreach ($array as $key => $item) {
                if (! $test($item, $key)) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * Returns true if any array item passes the callable condition.
     */
    public static function any(callable $test): Closure
    {
        return function ($array) use ($test) {
            foreach ($array as $key => $item) {
                if ($test($item, $key)) {
                    return true;
                }
            }

            return false;
        };
    }

    public static function reduce(callable $fn, mixed $initial = null): Closure
    {
        return fn ($array) => array_reduce($array, $fn, $initial);
    }
}

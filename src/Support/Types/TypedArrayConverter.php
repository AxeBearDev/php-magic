<?php

namespace AxeBear\Magic\Support\Types;

/**
 * Supports conversion of array types:
 *
 * Type[]
 * array<Type>
 * array<int, Type>
 * non-empty-array<Type>
 * non-empty-array<int, Type>
 * iterable<Type>
 * Collection<Type>
 * Collection<int, Type>
 */
class TypedArrayConverter implements ConvertsType
{
    public static function supports(string $type): bool
    {
        return (new TypedArrayType($type))->valid;
    }

    public static function convert(string $type, mixed $value): mixed
    {
        if (! settype($value, 'array')) {
            throw new \InvalidArgumentException('Value is not an array');
        }

        $type = new TypedArrayType($type);

        if (! $type->valid) {
            throw new \InvalidArgumentException("Unsupported type: {$type->fullType}");
        }

        if ($type->nonEmpty && empty($value)) {
            throw new \OutOfRangeException("Value is empty and type is not non-empty: {$type->fullType}");
        }

        $converted = [];

        foreach ($value as $key => $item) {
            $convertedKey = TypeConverter::convert($type->keyType, $key);
            $convertedItem = TypeConverter::convert($type->valueType, $item);
            $converted[$convertedKey] = $convertedItem;
        }

        return $converted;
    }
}

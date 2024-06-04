<?php

namespace AxeBear\Magic\Support\Types;

use AxeBear\Magic\Attributes\MagicProperty;
use AxeBear\Magic\Traits\MagicProperties;

/**
 * @property string $fullType
 * @property-read string $keyType
 * @property-read string $valueType
 * @property-read bool $nonEmpty
 * @property-read bool $valid
 */
class TypedArrayType
{
    use MagicProperties;

    public function __construct(
        protected string $fullType,
    ) {
        $this->bootAll();
    }

    #[MagicProperty]
    protected function nonEmpty(string $fullType): bool
    {
        return str_starts_with($fullType, 'non-empty');
    }

    #[MagicProperty]
    protected function wrappedType(string $fullType): string|bool
    {
        $fullType = trim($fullType);

        if (str_ends_with($fullType, '[]')) {
            return substr($fullType, 0, -2);
        }

        $firstBracket = strpos($fullType, '<');
        $lastBracket = strrpos($fullType, '>');
        if ($firstBracket === false || $lastBracket === false) {
            return false;
        }

        return trim(substr($fullType, $firstBracket + 1, $lastBracket - $firstBracket - 1));
    }

    #[MagicProperty]
    protected function keyType(string $wrappedType): string
    {
        $matches = [];
        if (! preg_match('/^(\w+),/', $wrappedType, $matches)) {
            return 'int';
        }

        return trim($matches[1]);
    }

    #[MagicProperty]
    protected function valueType(string $wrappedType): string|bool
    {
        // Remove the key type, if present, and return the rest
        $valueType = trim(preg_replace('/^(\w+),/', '', $wrappedType));

        return strlen($valueType) ? $valueType : false;
    }

    #[MagicProperty]
    protected function valid(string|bool $valueType): bool
    {
        return is_string($valueType);
    }
}

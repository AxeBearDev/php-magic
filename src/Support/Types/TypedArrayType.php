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
    protected function keyType(string $fullType): string
    {
        $matches = [];
        if (! preg_match('/<(\w+),/', $fullType, $matches)) {
            return 'int';
        }

        return trim($matches[1]);
    }

    #[MagicProperty]
    protected function valueType(string $fullType): string|bool
    {
        if (str_ends_with($fullType, '[]')) {
            return substr($fullType, 0, -2);
        }

        $matches = [];
        if (! preg_match('/(\w+)>$/', $fullType, $matches)) {
            return false;
        }

        return trim($matches[1]);
    }

    #[MagicProperty]
    protected function valid(string|bool $valueType): bool
    {
        return is_string($valueType);
    }
}

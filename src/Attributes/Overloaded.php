<?php

namespace AxeBear\Magic\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Overloaded
{
    /**
     * Maps this method to a magic method of the specified name.
     */
    public function __construct(
        public string $name,
    ) {
    }
}

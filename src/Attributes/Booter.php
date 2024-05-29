<?php

namespace AxeBear\Magic\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Booter
{
    /**
     * Marks a method as a booter method. Used by the Boots trait. Higher priority methods are booted first.
     *
     * @param  int  $priority Determines the sequence of booting. Higher numbers are booted first.
     */
    public function __construct(
        public int $priority = 0,
    ) {
    }
}

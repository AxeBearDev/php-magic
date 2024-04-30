<?php

namespace AxeBear\Magic;

use Attribute;

/**
 * Adds property getters to a class using the targeted method to provide the value.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Compute
{
    /**
     * Adds property getters for the targeted method.
     *
     * @param  array<string>  $aliases The aliases for the getter method
     */
    public function __construct(public array $aliases = [], public bool $useCache = false)
    {
    }
}

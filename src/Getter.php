<?php

namespace AxeBear\Magic;

use Attribute;

/**
 * Adds property getters to a class using the targeted method to provide the value.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Getter
{
    public static array $cache = [];

    use MakesClosures;

    /**
     * Adds property getters for the targeted method.
     *
     * @param  array<string>  $aliases The aliases for the getter method
     */
    public function __construct(public array $aliases = [])
    {
    }

    public function getValue(object|string $instance, string $methodName, string $alias): mixed
    {
        return $instance->$methodName();
    }
}

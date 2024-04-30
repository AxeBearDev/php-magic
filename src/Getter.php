<?php

namespace AxeBear\Magic;

use Attribute;
use ReflectionMethod;

/**
 * Adds property getters to a class using the targeted method to provide the value.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Getter
{
    use MakesClosures;

    /**
     * Adds property getters for the targeted method.
     *
     * @param  array<string>  $aliases The aliases for the getter method
     */
    public function __construct(public array $aliases = [])
    {
    }

    public function getValue(object|string $instance, ReflectionMethod $method): mixed
    {
        return $method->invoke($instance);
    }
}

<?php

namespace AxeBear\Magic\Attributes;

use Attribute;
use ReflectionProperty;

/**
 * Creates magic setters for all properties within the class of the specified visibility.
 * These magic setters will return the instance of the class, allowing for method chaining.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Fluent
{
    public function __construct(public int $visibility = ReflectionProperty::IS_PUBLIC)
    {
    }
}

<?php

namespace AxeBear\Magic;

use Attribute;
use ReflectionMethod;
use ReflectionParameter;

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
    public function __construct(public array $aliases = [], public bool $useCache = false)
    {
    }

    public function getArguments(object $instance, ReflectionMethod $method): array
    {
        $params = $method->getParameters() ?? [];

        return array_map(
            fn ($param) => $this->valueFromInstance($instance, $param),
            $params
        );
    }

    protected function valueFromInstance(object $instance, ReflectionParameter $param): mixed
    {
        $name = $param->getName();

        if (method_exists($instance, $name)) {
            return $instance->{$name}();
        }

        if (property_exists($instance, $name)) {
            return $instance->{$name};
        }

        throw new MagicException('Could not find class member '.$name.' to use as a parameter for the Getter method');
    }

    public function cacheKey(object $instance, ReflectionMethod $method): string
    {
        $args = $this->getArguments($instance, $method);

        return $method->getDeclaringClass()->getName().'::'.$method->getName().serialize($args);
    }

    public function getValue(object|string $instance, ReflectionMethod $method): mixed
    {
        $args = $this->getArguments($instance, $method);

        return $method->invoke($instance, ...$args);
    }
}

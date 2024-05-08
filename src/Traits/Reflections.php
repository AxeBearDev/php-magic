<?php

namespace AxeBear\Magic\Traits;

use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * Provides reflection utility methods.
 */
trait Reflections
{
    public function methodAllowsArguments(ReflectionMethod $method, array $arguments): bool
    {
        $params = $method->getParameters();
        $passedCount = count($arguments);
        $countMatches = $passedCount === count($params);
        $requiredCount = count(array_filter($params, fn ($param) => ! $param->isOptional()));

        // If there are more passed arguments than what this method requires, it's not a match.
        if (! $countMatches && $passedCount > $requiredCount && ! $method->isVariadic()) {
            return false;
        }

        // If there are fewer passed arguments than what this method requires, it's not a match.
        if (! $countMatches && $passedCount < $requiredCount) {
            return false;
        }

        foreach ($arguments as $index => $arg) {
            $param = $params[$index] ?? null;

            if (! $param && ! $method->isVariadic()) {
                return false;
            }

            if (! $this->parameterAllowsValue($param, $arg)) {
                return false;
            }
        }

        // Innocent unless proven guilty.
        return true;
    }

    /**
     * Tests whether the provided parameter should allow the provided value.
     */
    public function parameterAllowsValue(?ReflectionParameter $param, mixed $value): bool
    {
        if (! $param) {
            return false;
        }

        if ($value === null) {
            return $param->isOptional() || $param->allowsNull();
        }

        if (! $param->hasType()) {
            return true;
        }

        $type = $param->getType();

        if ($type instanceof ReflectionNamedType) {
            return $this->typeAllowsValue($type, $value);
        }

        if ($type instanceof ReflectionIntersectionType) {
            // Match all or fail
            foreach ($type->getTypes() as $subType) {
                if (! $this->typeAllowsValue($subType, $value)) {
                    return false;
                }
            }

            return true;
        }

        if ($type instanceof ReflectionUnionType) {
            // Match any or fail
            foreach ($type->getTypes() as $subType) {
                if ($this->typeAllowsValue($subType, $value)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Tests whether the provided type should allow the provided value.
     */
    public function typeAllowsValue(ReflectionNamedType $type, mixed $value): bool
    {
        $typeName = $type->getName();
        $valueType = get_debug_type($value);

        if ($typeName === 'iterable') {
            return is_iterable($value);
        }

        if ($typeName === 'mixed') {
            return true;
        }

        if (interface_exists($typeName) || class_exists($typeName)) {
            return $value instanceof $typeName;
        }

        return $valueType === $typeName;
    }
}

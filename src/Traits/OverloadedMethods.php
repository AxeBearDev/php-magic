<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Overloaded;
use AxeBear\Magic\Events\MagicCallEvent;
use AxeBear\Magic\Exceptions\MagicException;
use BadMethodCallException;
use ReflectionMethod;

/**
 * Combines any methods marked with a `Overloaded` attribute into a single magic method.
 */
trait OverloadedMethods
{
    use Magic;
    use Reflections;

    public function bootOverloadedMethods()
    {
        $overloads = $this->getOverloadedMethodsByName();
        foreach ($overloads as $name => $methods) {
            $this->registerOverloads($name, $methods);
        }
    }

    protected function registerOverloads(string $name, array $methods)
    {
        $this->onCall($name, function (MagicCallEvent $event) use ($methods) {
            $overload = $this->findOverloadedMethod($event, $methods);
            $event->setOutput($overload->invokeArgs($this, $event->arguments));
        });
    }

    /**
     * Finds the overloaded method that matches the types and count of arguments passed to the magic method.
     */
    protected function findOverloadedMethod(MagicCallEvent $event, array $methods): ?ReflectionMethod
    {
        $matches = array_filter(
            $methods,
            fn ($method) => $this->methodAllowsArguments($method, $event->arguments)
        );

        if (! $matches) {
            throw new BadMethodCallException(
                "No overloaded method found for '{$event->name}' that matches the arguments passed."
            );
        }

        if (count($matches) > 1) {
            $found = "'".implode("', '", array_map(fn ($method) => $method->getName(), $matches))."'";
            throw new BadMethodCallException(
                "Multiple overloaded methods ($found) found for '{$event->name}' that match the arguments passed."
            );
        }

        return reset($matches);
    }

    protected function getOverloadedMethodsByName()
    {
        $overloads = $this->getMagicMethods(Overloaded::class);
        $grouped = [];
        foreach ($overloads as [$method, $attributes]) {
            if (count($attributes) !== 1) {
                throw new MagicException('Overloaded methods must have exactly one Overloaded attribute');
            }
            $config = $attributes[0]->newInstance();
            $grouped[$config->name] ??= [];
            $grouped[$config->name][] = $method;
        }

        return $grouped;
    }
}

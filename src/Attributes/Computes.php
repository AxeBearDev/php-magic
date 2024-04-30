<?php

namespace AxeBear\Magic\Attributes;

use AxeBear\Magic\Magic;
use AxeBear\Magic\MagicEvent;
use AxeBear\Magic\MagicException;
use AxeBear\Magic\MakesClosures;
use ReflectionMethod;
use ReflectionParameter;

trait Computes
{
    use Magic;
    use MakesClosures;

    protected array $computeCache = [];

    public function bootComputes()
    {
        $this->eachMagicMethod(
            Compute::class,
            fn (ReflectionMethod $method, Compute $compute) => $this->registerComputes($method, $compute)
        );
    }

    /**
     * @param  array<ReflectionAttribute>  $computes
     */
    protected function registerComputes(ReflectionMethod $method, Compute $compute)
    {
        $aliases = $compute->aliases ? $compute->aliases : [$method->getName()];
        $onGet = function (MagicEvent $event) use ($compute, $method) {
            $args = $this->getArguments($method);

            if (! $compute->useCache) {
                $event->output($method->invoke($this, ...$args));

                return;
            }

            $cacheKey = $this->cacheKey($method, $args);
            $this->computeCache[$cacheKey] ??= $method->invoke($this, ...$args);
            $event->output($this->computeCache[$cacheKey]);
        };

        foreach ($aliases as $alias) {
            $this->onGet($alias, $onGet);
        }
    }

    protected function getArguments(ReflectionMethod $method): array
    {
        $params = $method->getParameters() ?? [];

        return array_map(
            fn ($param) => $this->valueFromInstance($param),
            $params
        );
    }

    protected function valueFromInstance(ReflectionParameter $param): mixed
    {
        $name = $param->getName();

        if (method_exists($this, $name)) {
            return $this->{$name}();
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new MagicException('Could not find class member '.$name.' to use as a parameter for the Getter method');
    }

    protected function cacheKey(ReflectionMethod $method, array $args): string
    {
        return $this::class.'::'.$method->getName().serialize($args);
    }
}

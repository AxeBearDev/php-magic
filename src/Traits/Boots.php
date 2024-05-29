<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Booter;
use AxeBear\Magic\Support\Arr;
use AxeBear\Magic\Support\Chain;
use stdClass;

/**
 * Calls any methods marked with the Booter attribute.
 */
trait Boots
{
    use Reflections;

    public function __construct(...$args)
    {
        $this->bootAll();
    }

    protected function bootAll(): void
    {
        $boots = $this->getBootMethods();

        foreach ($boots as $boot) {
            $boot->invoke($this);
        }
    }

    protected function getBootMethods(): array
    {
        $sorted = Chain::together(
            Arr::map(function ($found) {
                [$method, $attributes] = $found;
                $result = new stdClass();
                $result->method = $method;
                $result->priority = $attributes[0]->newInstance()->priority;

                return $result;
            }),
            Arr::sort(fn ($a, $b) => $b->priority <=> $a->priority),
            Arr::map(fn ($found) => $found->method),
        );

        $methods = $this->getMethodsWithAttribute(Booter::class);

        return $sorted($methods);
    }
}

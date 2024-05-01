<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Getter;
use AxeBear\Magic\Events\MagicEvent;
use ReflectionMethod;

trait Getters
{
    use Magic;

    public function bootGetters()
    {
        $this->eachMagicMethod(
            Getter::class,
            fn (ReflectionMethod $method, Getter $getter) => $this->registerGetters($method, $getter)
        );
    }

    /**
     * @param  array<ReflectionAttribute>  $getters
     */
    protected function registerGetters(ReflectionMethod $method, Getter $getter)
    {
        $aliases = $getter->aliases ? $getter->aliases : [$method->getName()];

        foreach ($aliases as $alias) {
            $this->onGet(
                $alias,
                function (MagicEvent $event) use ($method) {
                    $event->setOutput($method->invoke($this));
                }
            );
        }
    }
}

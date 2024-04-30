<?php

namespace AxeBear\Magic;

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
                function (MagicEvent $event) use ($getter, $method) {
                    $event->output($getter->getValue($this, $method));
                }
            );
        }
    }
}

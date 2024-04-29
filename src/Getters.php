<?php

namespace AxeBear\Magic;

use ReflectionMethod;

trait Getters
{
    use Magic;

    public function bootGetters()
    {
        $methods = $this->getMagicMethods(Getter::class);
        foreach ($methods as [$method, $getters]) {
            $this->registerGetters($method, $getters);
        }
    }

    /**
     * Undocumented function
     *
     * @param  array<ReflectionAttribute>  $getters
     * @return void
     */
    protected function registerGetters(ReflectionMethod $method, array $getters)
    {
        if (! $method->isPublic()) {
            throw new MagicException('Getter methods must be public');
        }

        [$attribute] = $getters; // Only one getter per method
        $getter = $attribute->newInstance();
        $methodName = $method->getName();
        $aliases = $getter->aliases ? $getter->aliases : [$methodName];

        foreach ($aliases as $alias) {
            $this->onGet(
                $alias,
                function (MagicEvent $event) use ($getter, $methodName, $alias) {
                    $event->output($getter->getValue($this, $methodName, $alias));
                }
            );
        }
    }
}

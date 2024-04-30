<?php

namespace AxeBear\Magic;

use ReflectionMethod;

trait Getters
{
    use Magic;

    protected array $getterCache = [];

    public function bootGetters()
    {
        $methods = $this->getMagicMethods(Getter::class);
        foreach ($methods as [$method, $getters]) {
            $this->registerGetters($method, $getters);
        }
    }

    /**
     * @param  array<ReflectionAttribute>  $getters
     */
    protected function registerGetters(ReflectionMethod $method, array $getters)
    {
        [$attribute] = $getters; // Only one getter per method
        $getter = $attribute->newInstance();
        $aliases = $getter->aliases ? $getter->aliases : [$method->getName()];

        foreach ($aliases as $alias) {
            $this->onGet(
                $alias,
                function (MagicEvent $event) use ($getter, $method) {
                    if (! $getter->useCache) {
                        $event->output($getter->getValue($this, $method));

                        return;
                    }

                    $cacheKey = $getter->cacheKey($this, $method);
                    $this->getterCache[$cacheKey] ??= $getter->getValue($this, $method);

                    $event->output($this->getterCache[$cacheKey]);
                }
            );
        }
    }
}

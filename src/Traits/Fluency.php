<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Fluent;
use AxeBear\Magic\Events\MagicEvent;
use BadMethodCallException;
use ReflectionClass;

trait Fluency
{
    use Magic;

    public function bootFluency()
    {
        $reflection = new ReflectionClass($this);
        $visibility = $this->getFluencyVisibility();
        $props = $reflection->getProperties($visibility);

        foreach ($props as $prop) {
            $this->onCall($prop->getName(), function (MagicEvent $event) use ($prop) {
                if (! is_array($event->input) || count($event->input) !== 1) {
                    throw new BadMethodCallException("Method {$event->name} expects exactly one argument.");
                }
                [$value] = $event->input;

                $prop->setValue($this, $value);
                $event->output($this);
            });
        }
    }

    public function getFluencyVisibility(): int
    {
        $reflection = new ReflectionClass($this);
        $fluents = $reflection->getAttributes(Fluent::class);
        $fluent = $fluents ? $fluents[0]->newInstance() : new Fluent();

        return $fluent->visibility;
    }
}

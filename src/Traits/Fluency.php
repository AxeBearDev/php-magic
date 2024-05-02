<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Fluent;
use AxeBear\Magic\Events\MagicCallEvent;
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
            $this->onCall($prop->getName(), function (MagicCallEvent $event) use ($prop) {
                if (! is_array($event->arguments) || count($event->arguments) > 1) {
                    throw new BadMethodCallException("Method {$event->name} expects zero or one argument.");
                }

                if (count($event->arguments) === 0) {
                    $event->setOutput($prop->getValue($this));

                    return;
                }

                [$value] = $event->arguments;

                $prop->setValue($this, $value);
                $event->setOutput($this);
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

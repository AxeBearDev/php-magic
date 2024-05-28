<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Events\MagicCallEvent;
use AxeBear\Magic\Events\MagicEvent;
use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Exceptions\MagicException;
use AxeBear\Magic\Support\Chain;
use AxeBear\Magic\Support\EventRegistry;
use Closure;
use ReflectionClass;

/**
 * Allows merging of many magic method overrides.
 * Also handles booting traits in the constructor.
 *
 * @template MagicEventHandler of (MagicEvent) => void
 * @template MagicEventHandlers of array<string, array<MagicEventHandler>>
 */
trait Magic
{
    use BootsTraits;

    public readonly EventRegistry $beforeGet;

    public readonly EventRegistry $onGet;

    public readonly EventRegistry $afterGet;

    public readonly EventRegistry $beforeSet;

    public readonly EventRegistry $onSet;

    public readonly EventRegistry $afterSet;

    public readonly EventRegistry $beforeCall;

    public readonly EventRegistry $onCall;

    public readonly EventRegistry $afterCall;

    protected function initMagic(): void
    {
        $this->beforeGet = new EventRegistry();
        $this->onGet = new EventRegistry();
        $this->afterGet = new EventRegistry();

        $this->beforeSet = new EventRegistry();
        $this->onSet = new EventRegistry();
        $this->afterSet = new EventRegistry();

        $this->beforeCall = new EventRegistry();
        $this->onCall = new EventRegistry();
        $this->afterCall = new EventRegistry();
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

    public function __call(string $name, array $arguments)
    {
        $event = new MagicCallEvent($name, $arguments);
        $fallback = fn () => parent::__call($name, $arguments);

        return $this->useMagic(
            $event, [
                $this->beforeCall,
                $this->onCall,
                $this->afterCall,
            ],
            $fallback
        );
    }

    public function __get(string $name)
    {
        $event = new MagicGetEvent($name);
        $fallback = fn () => parent::__get($name);

        return $this->useMagic(
            $event, [
                $this->beforeGet,
                $this->onGet,
                $this->afterGet,
            ],
            $fallback
        );
    }

    public function __set(string $name, mixed $value)
    {
        $event = new MagicSetEvent($name, $value);
        $fallback = fn () => parent::__set($name, $value);

        return $this->useMagic(
            $event, [
                $this->beforeSet,
                $this->onSet,
                $this->afterSet,
            ],
            $fallback
        );
    }

    public function __isset(string $name): bool
    {
        return $this->onGet->has($name) || (class_parents($this) && parent::__isset($name));
    }

    public function __unset(string $name): void
    {
        if ($this->onSet->handles($name)) {
            $this->onSet->unset($name);
        } elseif (class_parents($this)) {
            parent::__unset($name);
        }
    }

    /**
     * Attempts to use the handlers to process the event. If none of the handlers stop the event or
     * provide output, the fallback closure is called
     *
     * @param  EventRegistry  $before The registry of before handlers.
     * @param  EventRegistry  $on The registry of on handlers.
     * @param  EventRegistry  $after The registry of after handlers.
     * @param  fn (): mixed  $fallback The parent fallback to call if no handlers are found.
     * @return void
     */
    protected function useMagic(
      MagicEvent $event,
      EventRegistry $before,
      EventRegistry $on,
      EventRegistry $after,
      Closure $fallback)
    {
        $fallback = (bool) class_parents($this::class)
          ? $fallback
          : fn () => throw new MagicException('No handlers found for '.$event->name);

        $befores = $before->find($event->name);
        $ons = $on->find($event->name);
        $afters = $after->find($event->name);

        $handleBefore = Chain::together(...$befores)
          ->carryInput()
          ->until(fn (MagicEvent $event) => $event->stopped)
          ->then(fn (MagicEvent $event) => $event->getOutput());

        $handleOn = Chain::together(...$ons)
          ->carryInput()
          ->until(fn (MagicEvent $event) => $event->stopped)
          ->then(fn (MagicEvent $event) => $event->getOutput());

        $handleAfter = Chain::together(...$afters)
          ->carryInput()
          ->until(fn (MagicEvent $event) => $event->stopped);

        $preparedEvent = $handleBefore($event);
        $result = $handleOn($preparedEvent);
        $handleAfter($result);

        return $result->getOutput();
    }

    /**
     * Calls the provided callback for each method that has the specified attribute.
     *
     * @param fn (ReflectionMethod, Attribute) $callback
     */
    public function eachMagicMethod(string $attributeName, Closure $callback): void
    {
        foreach ($this->getMagicMethods($attributeName) as [$method, $attributes]) {
            foreach ($attributes as $attribute) {
                $callback($method, $attribute->newInstance());
            }
        }
    }

    /**
     * Calls the provided callback for each property that has the specified attribute.
     *
     * @param fn (ReflectionMethod, Attribute) $callback
     */
    public function eachMagicProperty(string $attributeName, Closure $callback): void
    {
        foreach ($this->getMagicProperties($attributeName) as [$property, $attributes]) {
            foreach ($attributes as $attribute) {
                $callback($property, $attribute->newInstance());
            }
        }
    }

    /**
     * Gets the methods that have the specified attribute
     *
     * @return array<array{0: \ReflectionMethod, 1: array<\ReflectionAttribute>}>
     */
    public function getMagicMethods(string $attributeName): array
    {
        $reflection = new ReflectionClass($this);

        return $this->collectMagicAttributes($attributeName, $reflection->getMethods());
    }

    /**
     * Gets the properties that have the specified attribute
     *
     * @return array<array{0: \ReflectionProperty, 1: array<\ReflectionAttribute>}>
     */
    public function getMagicProperties(string $attributeName): array
    {
        $reflection = new ReflectionClass($this);

        return $this->collectMagicAttributes($attributeName, $reflection->getProperties());
    }

    protected function collectMagicAttributes(string $attributeName, array $items): array
    {
        $collected = [];

        foreach ($items as $item) {
            $attributes = $item->getAttributes($attributeName);
            if ($attributes) {
                $collected[] = [$item, $attributes];
            }
        }

        return $collected;
    }
}

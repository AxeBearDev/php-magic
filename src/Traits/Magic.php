<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Booter;
use AxeBear\Magic\Events\MagicCallEvent;
use AxeBear\Magic\Events\MagicEvent;
use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Exceptions\MagicException;
use AxeBear\Magic\Support\Chain;
use AxeBear\Magic\Support\EventRegistry;
use Closure;

/**
 * Allows merging of many magic method overrides.
 * Also handles booting traits in the constructor.
 *
 * @template MagicEventHandler of (MagicEvent) => void
 * @template MagicEventHandlers of array<string, array<MagicEventHandler>>
 */
trait Magic
{
    use Boots, Reflections;

    public readonly EventRegistry $beforeMagicGet;

    public readonly EventRegistry $onMagicGet;

    public readonly EventRegistry $afterMagicGet;

    public readonly EventRegistry $beforeMagicSet;

    public readonly EventRegistry $onMagicSet;

    public readonly EventRegistry $afterMagicSet;

    public readonly EventRegistry $beforeMagicCall;

    public readonly EventRegistry $onMagicCall;

    public readonly EventRegistry $afterMagicCall;

    // All other traits in this domain rely on this booter to be called first
    #[Booter(PHP_INT_MAX)]
    protected function bootMagic(): void
    {
        $this->beforeMagicGet = new EventRegistry();
        $this->onMagicGet = new EventRegistry();
        $this->afterMagicGet = new EventRegistry();

        $this->beforeMagicSet = new EventRegistry();
        $this->onMagicSet = new EventRegistry();
        $this->afterMagicSet = new EventRegistry();

        $this->beforeMagicCall = new EventRegistry();
        $this->onMagicCall = new EventRegistry();
        $this->afterMagicCall = new EventRegistry();
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
        if ($this->isInvokableProperty($name)) {
            return ($this->$name)(...$arguments);
        }

        $event = new MagicCallEvent($name, $arguments);

        return $this->useMagic(
            $event,
            $this->beforeMagicCall,
            $this->onMagicCall,
            $this->afterMagicCall,
        );
    }

    public function __get(string $name)
    {
        $event = new MagicGetEvent($name);

        return $this->useMagic(
            $event,
            $this->beforeMagicGet,
            $this->onMagicGet,
            $this->afterMagicGet,
        );
    }

    public function __set(string $name, mixed $value)
    {
        $event = new MagicSetEvent($name, $value);

        return $this->useMagic(
            $event,
            $this->beforeMagicSet,
            $this->onMagicSet,
            $this->afterMagicSet,
        );
    }

    public function __isset(string $name): bool
    {
        return $this->onMagicGet->has($name) || (class_parents($this) && parent::__isset($name));
    }

    public function __unset(string $name): void
    {
        if ($this->onMagicSet->handles($name)) {
            $this->onMagicSet->unset($name);
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
      EventRegistry $after)
    {
        $befores = $before->find($event->name);
        $ons = $on->find($event->name);
        $afters = $after->find($event->name);

        if (! $ons) {
            $eventType = $event::class;
            throw new MagicException("No {$eventType} handler found for {$event->name}");
        }

        $handleBefore = Chain::together(...$befores)
          ->carryInput()
          ->until(fn (MagicEvent $event) => $event->stopped);

        $handleOn = Chain::together(...$ons)
          ->carryInput()
          ->until(fn (MagicEvent $event) => $event->stopped);

        $handleAfter = Chain::together(...$afters)
          ->carryInput()
          ->until(fn (MagicEvent $event) => $event->stopped);

        $handleBefore($event);
        $handleOn($event);
        $handleAfter($event);

        return $event->getOutput();
    }

    /**
     * Calls the provided callback for each method that has the specified attribute.
     *
     * @param fn (ReflectionMethod, Attribute) $callback
     */
    public function eachMagicMethod(string $attributeName, Closure $callback): void
    {
        foreach ($this->getMethodsWithAttribute($attributeName) as [$method, $attributes]) {
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
        foreach ($this->getPropertiesWithAttribute($attributeName) as [$property, $attributes]) {
            foreach ($attributes as $attribute) {
                $callback($property, $attribute->newInstance());
            }
        }
    }
}

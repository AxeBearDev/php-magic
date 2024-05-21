<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Events\MagicCallEvent;
use AxeBear\Magic\Events\MagicEvent;
use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Exceptions\MagicException;
use AxeBear\Magic\Support\Chain;
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

    /* @var MagicEventHandlers */
    private array $callers = [];

    /* @var MagicEventHandlers */
    private static array $staticCallers = [];

    /* @var MagicEventHandlers */
    private array $getters = [];

    /* @var MagicEventHandlers */
    private array $setters = [];

    public static function hasMagicStaticCaller(string $name): bool
    {
        return isset(static::$staticCallers[$name]);
    }

    public function hasAnyMagic(string $name): bool
    {
        return $this->hasMagicGetter($name) || $this->hasMagicSetter($name) || $this->hasMagicCaller($name);
    }

    public function hasMagicGetter(string $name): bool
    {
        return isset($this->getters[$name]);
    }

    public function hasMagicSetter(string $name): bool
    {
        return isset($this->setters[$name]);
    }

    public function hasMagicCaller(string $name): bool
    {
        return isset($this->callers[$name]);
    }

    /**
     * Register handlers for calls to __call with names that match the specified pattern. Patterns
     * are matched using the fnmatch function.
     */
    public function onCall(string $pattern, Closure ...$handlers): static
    {
        $this->callers[$pattern] = [...$this->callers[$pattern] ?? [], ...$handlers];

        return $this;
    }

    /**
     * Register handlers for calls to __callStatic with names that match the specified pattern. Patterns
     * are matched using the fnmatch function.
     *
     * @param  MagicEventHandler  ...$handlers
     */
    public static function onStaticCall(string $pattern, Closure ...$handlers): void
    {
        static::$staticCallers[$pattern] = [...static::$staticCallers[$pattern] ?? [], ...$handlers];
    }

    /**
     * Register handlers for calls to __get with names that match the specified pattern. Patterns
     * are matched using the fnmatch function.
     *
     * @param  MagicEventHandler  ...$handlers
     */
    public function onGet(string $pattern, Closure ...$handlers): static
    {
        $this->getters[$pattern] = [...$this->getters[$pattern] ?? [], ...$handlers];

        return $this;
    }

    /**
     * Register handlers for calls to __set with names that match the specified pattern. Patterns
     * are matched using the fnmatch function.
     *
     * @param  MagicEventHandler  ...$handlers
     */
    public function onSet(string $pattern, Closure ...$handlers): static
    {
        $this->setters[$pattern] = [...$this->setters[$pattern] ?? [], ...$handlers];

        return $this;
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
        $callers = self::findMagicHandlers($name, $this->callers);
        $fallback = fn () => parent::__call($name, $arguments);

        return static::useMagic($event, $callers, $fallback);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $event = new MagicCallEvent($name, $arguments);
        $callers = self::findMagicHandlers($name, static::$staticCallers);
        $fallback = fn () => parent::__callStatic($name, $arguments);

        return static::useMagic($event, $callers, $fallback);
    }

    public function __get(string $name)
    {
        $event = new MagicGetEvent($name);
        $getters = self::findMagicHandlers($name, $this->getters);
        $fallback = fn () => parent::__get($name);

        return static::useMagic($event, $getters, $fallback);
    }

    public function __set(string $name, mixed $value)
    {
        $event = new MagicSetEvent($name, $value);
        $setters = self::findMagicHandlers($name, $this->setters);
        $fallback = fn () => parent::__set($name, $value);

        return static::useMagic($event, $setters, $fallback);
    }

    public function __isset(string $name): bool
    {
        return isset($this->getters[$name]) || (class_parents($this) && parent::__isset($name));
    }

    public function __unset(string $name): void
    {
        if (isset($this->setters[$name])) {
            unset($this->setters[$name]);
        } elseif (class_parents($this)) {
            parent::__unset($name);
        }
    }

    /**
     * Collects the handlers for a magic event based on the name of the member called.
     *
     * @param  MagicEventHandlers  $handlers
     * @return MagicEventHandler[]
     */
    protected static function findMagicHandlers(string $search, array $groups): array
    {
        $found = [];

        foreach ($groups as $pattern => $handlers) {
            if (fnmatch($pattern, $search)) {
                $found = [...$found, ...$handlers];
            }
        }

        return $found;
    }

    /**
     * Attempts to use the handlers to process the event. If none of the handlers stop the event or
     * provide output, the fallback closure is called
     *
     * @param  string  $type
     * @param  MagicEventHandlers[]  $handlers
     * @param  fn (): mixed  $fallback The parent fallback to call if no handlers are found.
     * @return void
     */
    protected static function useMagic(MagicEvent $event, array $handlers, Closure $fallback)
    {
        $fallback = (bool) class_parents(self::class)
          ? $fallback
          : fn () => throw new MagicException('No handlers found for '.$event->name);

        if (! $handlers) {
            return $fallback();
        }

        $chain = Chain::together(...$handlers)
          ->carryInput()
          ->until(fn (MagicEvent $event) => $event->stopped)
          ->then(fn (MagicEvent $event) => $event->getOutput());

        return $chain($event);
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

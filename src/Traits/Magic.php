<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Events\MagicCallEvent;
use AxeBear\Magic\Events\MagicEvent;
use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Exceptions\MagicException;
use Closure;
use ReflectionClass;

/**
 * Allows merging of many magic method overrides.
 * Also handles booting traits in the constructor.
 */
trait Magic
{
    use BootsTraits;

    /* @var array<callable(MagicEvent): void> */
    private array $callers = [];

    /* @var array<callable(MagicEvent): void> */
    private static array $staticCallers = [];

    /* @var array<callable(MagicEvent): void> */
    private array $getters = [];

    /* @var array<callable(MagicEvent): void> */
    private array $setters = [];

    public function onCall(string $name, Closure ...$handlers): void
    {
        $this->callers[$name] = [...$this->callers[$name] ?? [], ...$handlers];
    }

    public static function onStaticCall(string $name, Closure ...$handlers): void
    {
        static::$staticCallers[$name] = [...static::$staticCallers[$name] ?? [], ...$handlers];
    }

    public function onGet(string $name, Closure ...$handlers): void
    {
        $this->getters[$name] = [...$this->getters[$name] ?? [], ...$handlers];
    }

    public function onSet(string $name, Closure ...$handlers): void
    {
        $this->setters[$name] = [...$this->setters[$name] ?? [], ...$handlers];
    }

    public function __call(string $name, array $arguments)
    {
        $event = new MagicCallEvent($name, $arguments);
        $callers = $this->callers[$name] ?? [];
        $fallback = fn () => parent::__call($name, $arguments);

        return static::useMagic('__call', $event, $callers, $fallback);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $event = new MagicCallEvent($name, $arguments);
        $callers = static::$staticCallers[$name] ?? [];
        $fallback = fn () => parent::__callStatic($name, $arguments);

        return static::useMagic('__callStatic', $event, $callers, $fallback);
    }

    public function __get(string $name)
    {
        $event = new MagicGetEvent($name);
        $getters = $this->getters[$name] ?? [];
        $fallback = fn () => parent::__get($name);

        return static::useMagic('__get', $event, $getters, $fallback);
    }

    public function __set(string $name, mixed $value)
    {
        $event = new MagicSetEvent($name, $value);
        $setters = $this->setters[$name] ?? [];
        $fallback = fn () => parent::__set($name, $value);

        return static::useMagic('__set', $event, $setters, $fallback);
    }

    protected static function useMagic(string $type, MagicEvent $event, array $handlers, Closure $fallback)
    {
        foreach ($handlers as $handler) {
            $handler($event);

            if (! $event->stopped) {
                continue;
            }

            if ($event->hasOutput()) {
                return $event->getOutput();
            } else {
                return;
            }
        }

        if ($event->hasOutput()) {
            return $event->getOutput();
        }

        if (class_parents(self::class)) {
            return $fallback();
        }

        throw new MagicException('No '.$type.' handler found for '.static::class.'::'.$event->name);
    }

    public function eachMagicMethod(string $attributeName, Closure $callback): void
    {
        foreach ($this->getMagicMethods($attributeName) as [$method, $attributes]) {
            foreach ($attributes as $attribute) {
                $callback($method, $attribute->newInstance());
            }
        }
    }

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

<?php

namespace AxeBear\Magic;

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
    protected array $callers = [];

    /* @var array<callable(MagicEvent): void> */
    protected static array $staticCallers = [];

    /* @var array<callable(MagicEvent): void> */
    protected array $getters = [];

    /* @var array<callable(MagicEvent): void> */
    protected array $setters = [];

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
        $event = new MagicEvent(__METHOD__, $name, $arguments);
        $callers = $this->callers[$name] ?? [];
        $fallback = fn () => parent::__call($name, $arguments);

        return static::useMagic($this, $event, $callers, $fallback);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $event = new MagicEvent(__METHOD__, $name, $arguments);
        $callers = static::$staticCallers[$name] ?? [];
        $fallback = fn () => parent::__callStatic($name, $arguments);

        return static::useMagic(static::class, $event, $callers, $fallback);
    }

    public function __get(string $name)
    {
        $event = new MagicEvent(__METHOD__, $name);
        $getters = $this->getters[$name] ?? [];
        $fallback = fn () => parent::__get($name);

        return static::useMagic($this, $event, $getters, $fallback);
    }

    public function __set(string $name, mixed $value)
    {
        $event = new MagicEvent(__METHOD__, $name, $value);
        $setters = $this->setters[$name] ?? [];
        $fallback = fn () => parent::__set($name, $value);

        return static::useMagic($this, $event, $setters, $fallback);
    }

    protected static function useMagic(object|string $context, MagicEvent $event, array $handlers, Closure $fallback)
    {
        foreach ($handlers as $handler) {
            $handler($event);

            if (! $event->stopped) {
                continue;
            }

            if ($event->hasOutput()) {
                return $event->output;
            } else {
                return;
            }
        }

        if ($event->hasOutput()) {
            return $event->output;
        }

        if (class_parents(self::class)) {
            return $fallback();
        }

        throw new MagicException('No '.$event->type.' handler found for '.static::class.'::'.$event->name);
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

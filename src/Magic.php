<?php

namespace AxeBear\Magic;

use Closure;

/**
 * Allows merging of many magic method overrides.
 * Also handles booting traits in the constructor.
 */
trait Magic
{
    use BootsTraits;

    /* @var fn (MagicEvent): void[] */
    protected array $callers = [];

    /* @var fn (MagicEvent): void[] */
    protected static array $staticCallers = [];

    /* @var fn (MagicEvent): void[] */
    protected array $getters = [];

    /* @var fn (MagicEvent): void[] */
    protected array $setters = [];

    public function onCall(string $name, Closure ...$handlers): void
    {
        $this->callers[$name] = array_merge($this->callers[$name] ?? [], $handlers);
    }

    public static function onStaticCall(string $name, Closure ...$handlers): void
    {
        static::$staticCallers[$name] = array_merge(static::$staticCallers[$name] ?? [], $handlers);
    }

    public function onGet(string $name, Closure ...$handlers): void
    {
        $this->getters[$name] = array_merge($this->getters[$name] ?? [], $handlers);
    }

    public function onSet(string $name, Closure ...$handlers): void
    {
        $this->setters[$name] = array_merge($this->setters[$name] ?? [], $handlers);
    }

    public function __call(string $name, array $arguments)
    {
        $event = new MagicEvent('__call', $name, $arguments);
        $callers = $this->callers[$name] ?? [];
        $fallback = fn () => parent::__call($name, $arguments);

        return static::useMagic($event, $callers, $fallback);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $event = new MagicEvent('__callStatic', $name, $arguments);
        $callers = static::$staticCallers[$name] ?? [];
        $fallback = fn () => parent::__callStatic($name, $arguments);

        return static::useMagic($event, $callers, $fallback);
    }

    public function __get(string $name)
    {
        $event = new MagicEvent('__get', $name);
        $getters = $this->getters[$name] ?? [];
        $fallback = fn () => parent::__get($name);

        return static::useMagic($event, $getters, $fallback);
    }

    public function __set(string $name, mixed $value)
    {
        $event = new MagicEvent('__set', $name, $value);
        $setters = $this->setters[$name] ?? [];
        $fallback = fn () => parent::__set($name, $value);

        return static::useMagic($event, $setters, $fallback);
    }

    protected static function useMagic(MagicEvent $event, array $handlers, Closure $fallback)
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

        throw new MagicException('Call to undefined method or property '.static::class.'::'.$event->name);
    }
}

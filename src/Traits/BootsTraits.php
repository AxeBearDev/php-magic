<?php

namespace AxeBear\Magic\Traits;

use ReflectionClass;

/**
 * Calls a "boot{ClassName}" method on each trait used by the class.
 * Also allows for booting traits statically by calling the "bootStatic{ClassName}"
 * method. Internally tracks which traits have been booted to prevent duplicate calls.
 */
trait BootsTraits
{
    protected bool $bootedInstanceTraits = false;

    protected static bool $bootedStaticTraits = false;

    public function __construct(...$args)
    {
        if (get_parent_class($this)) {
            parent::__construct(...$args);
        }

        $this->bootInstanceTraits();
    }

    /**
     * Boots any traits that include a boot{ClassName} method.
     */
    protected function bootInstanceTraits(): void
    {
        if ($this->bootedInstanceTraits) {
            return;
        }
        static::bootTraits('boot', $this);
        $this->bootedInstanceTraits = true;
    }

    /**
     * Boots any traits that include a static bootStatic{ClassName} method
     */
    protected static function bootStaticTraits(): void
    {
        if (static::$bootedStaticTraits) {
            return;
        }
        static::bootTraits('boot', null);
        static::$bootedStaticTraits = true;
    }

    private static function bootTraits(string $prefix, ?object $context): void
    {
        $traits = static::traits();

        foreach ($traits as $trait) {
            $reflection = new ReflectionClass($trait);
            $shortName = $reflection->getShortName();
            $booter = $prefix.$shortName;

            if (! $reflection->hasMethod($booter)) {
                continue;
            }

            if ($context) {
                $context->{$booter}();
            } else {
                $trait::{$booter}();
            }
        }
    }

    /**
     * Gets a list of traits used by the class or its parents.
     */
    public static function traits(): array
    {
        $class = static::class;
        $traits = [];
        do {
            $traits = [...$traits, ...class_uses($class)];
        } while ($class = get_parent_class($class));

        return $traits;
    }
}

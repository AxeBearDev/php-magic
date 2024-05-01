<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Exceptions\MagicException;
use Closure;

trait MakesClosures
{
    /**
     * Converts a callable handler to a closure bound to the provided context (if applicable)
     */
    public static function makeClosure(object|string $instance, callable|string $handler): Closure
    {
        if (method_exists($instance, $handler)) {
            $callable = Closure::fromCallable([$instance, $handler]);
        } else {
            $callable = Closure::fromCallable($handler);
        }

        if (! is_callable($callable)) {
            throw new MagicException('Method '.$handler.' is not callable');
        }

        return $callable;
    }
}

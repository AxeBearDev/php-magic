<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Property;
use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Exceptions\MagicException;
use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

trait Properties
{
    use Magic;
    use ParsesDocs;

    protected array $registeredProperties = [];

    protected array $propertyCache = [];

    public function getRawValue(string $name, ?Closure $default = null)
    {
        $reflection = new ReflectionClass($this);
        $default ??= fn () => null;
        $prop = $reflection->hasProperty($name) ? $reflection->getProperty($name) : null;

        return $prop ? $prop->getValue($this) : $default();
    }

    protected function bootProperties()
    {
        $this->eachMagicProperty(
            Property::class,
            fn ($property, $config) => $this->registerMagicProperty($property, $config)
        );

        $this->eachMagicMethod(
            Property::class,
            fn ($method, $config) => $this->registerMagicMethod($method, $config)
        );

        $this->registerClassProperties();
    }

    protected function registerMagicMethod(ReflectionMethod $method, Property $config)
    {
        $aliases = $config->aliases ? $config->aliases : [$method->getName()];

        if ($config->onGet || $config->onSet) {
            throw new MagicException('Cannot use onGet or onSet with a magic method: '.$method->name);
        }

        $onGet = function (MagicGetEvent $event) use ($config, $method) {
            $args = $this->getArguments($method);
            $get = fn () => $method->invoke($this, ...$args);

            if ($config->disableCache) {
                $output = $get();
            } else {
                $cacheKey = $this->cacheKey($method, $args);
                $this->propertyCache[$cacheKey] ??= $get();
                $output = $this->propertyCache[$cacheKey];
            }

            $event->setOutput($output);
        };

        foreach ($aliases as $alias) {
            $this->onGet($alias, $onGet);
        }

        $this->registeredProperties[$method->name] = $config;
    }

    protected function registerMagicProperty(ReflectionProperty $property, Property $config)
    {
        if ($property->isPublic()) {
            throw new MagicException('Magic is not available for public properties: '.$property->name);
        }

        $aliases = $config->aliases ? $config->aliases : [$property->getName()];

        foreach ($aliases as $alias) {
            if ($config->readable()) {
                $this->onGet(
                    $alias,
                    function (MagicGetEvent $event) use ($property, $config) {
                        $value = $event->getOutput(fn () => $property->getValue($this));
                        foreach ($config->onGet as $transform) {
                            $value = $transform($value);
                        }
                        $event->setOutput($value);
                    }
                );
            }

            if ($config->writable()) {
                $this->onSet(
                    $alias,
                    function (MagicSetEvent $event) use ($property, $config) {
                        $value = $event->getOutput(fn () => $event->value);
                        foreach ($config->onSet as $transform) {
                            $value = $transform($value);
                        }
                        $property->setValue($this, $value);
                    }
                );
            }
        }

        $this->registeredProperties[$property->name] = $config;
    }

    protected function registerClassProperties()
    {
        $doc = $this->getDocNode();
        $this->registerTaggedProperties(
            $doc->getTagsByName('@property'),
            new Property(access: Property::READ_WRITE)
        );

        $this->registerTaggedProperties(
            $doc->getTagsByName('@property-read'),
            new Property(access: Property::READ)
        );

        $this->registerTaggedProperties(
            $doc->getTagsByName('@property-write'),
            new Property(access: Property::WRITE)
        );
    }

    /**
     * Register properties from the @property tags.
     *
     * @param  \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode[]  $tags
     */
    protected function registerTaggedProperties(array $tags, Property $config): void
    {
        $reflection = new ReflectionClass($this);
        foreach ($tags as $tag) {
            $name = ltrim($tag->value->propertyName, '$');

            if ($this->registeredProperties[$name] ?? false) {
                // Already registered. Yay!
                continue;
            }

            // Try to find the protected property first
            $prop = $reflection->hasProperty($name) ? $reflection->getProperty($name) : null;
            if ($prop && ! $prop->isPublic()) {
                $this->registerMagicProperty($prop, $config);

                continue;
            }

            // Then try to find a method with the same name
            $method = $reflection->hasMethod($name) ? $reflection->getMethod($name) : null;
            if ($method) {
                $this->registerMagicMethod($method, $config);

                continue;
            }

            // If we can't find a matching method or property, that's an error!
            throw new MagicException('No member named '.$name.' to use as a property. Check your docblock tags.');
        }
    }

    protected function getArguments(ReflectionMethod $method): array
    {
        $params = $method->getParameters() ?? [];

        return array_map(
            fn ($param) => $this->valueFromInstance($param),
            $params
        );
    }

    protected function valueFromInstance(ReflectionParameter $param): mixed
    {
        $name = $param->getName();

        if (method_exists($this, $name)) {
            return $this->{$name}();
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new MagicException('Could not find class member '.$name.' to use as a parameter for the Getter method');
    }

    protected function cacheKey(ReflectionMethod $method, array $args): string
    {
        return $this::class.'::'.$method->getName().serialize($args);
    }
}

<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Property;
use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Exceptions\MagicException;
use Closure;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

trait Properties
{
    use Magic;
    use MakesClosures;
    use ParsesDocs;

    private array $propertyCache = [];

    private array $unboundProperties = [];

    public function getRawValue(string $name, ?Closure $default = null)
    {
        if (isset($this->unboundProperties[$name])) {
            return $this->unboundProperties[$name];
        }

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
                        $value = $this->valueAfterTransforms(
                            $event->getOutput(fn () => $property->getValue($this)),
                            $config->onGet
                        );
                        $event->setOutput($value);
                    }
                );
            }

            if ($config->writable()) {
                $this->onSet(
                    $alias,
                    function (MagicSetEvent $event) use ($property, $config) {
                        $value = $this->valueAfterTransforms(
                            $event->getOutput(fn () => $event->value),
                            $config->onSet
                        );
                        $property->setValue($this, $value);
                    }
                );
            }
        }
    }

    /**
     * Registers a property this isn't bound to a class method or property.
     *
     * @return void
     */
    protected function registerUnboundProperty(PhpDocTagNode $tag, Property $config)
    {
        // TODO: Add type coercion
        $name = ltrim($tag->value->propertyName, '$');
        $type = $tag->value->type?->name ?? null;

        if ($config->readable()) {
            $this->onGet(
                $name,
                function (MagicGetEvent $event) use ($name, $config) {
                    $value = $this->valueAfterTransforms($this->getRawValue($name), $config->onGet);
                    $event->setOutput($value);
                }
            );
        }

        if ($config->writable()) {
            $this->onSet(
                $name,
                function (MagicSetEvent $event) use ($name, $type, $config) {
                    if ($type) {
                        $event->value = $this->coerceType($event->value, $type);
                    }
                    $value = $this->valueAfterTransforms($event->value, $config->onSet);
                    $this->unboundProperties[$name] = $value;
                }
            );
        }
    }

    public function coerceType(mixed $value, string $type): mixed
    {
        settype($value, $type);

        return $value;
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

            if ($this->hasAnyMagic($name)) {
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

            // If we can't find a matching method or property, track the value in the properties array
            $this->registerUnboundProperty($tag, $config);
        }
    }

    private function getArguments(ReflectionMethod $method): array
    {
        $params = $method->getParameters() ?? [];

        return array_map(
            fn ($param) => $this->valueFromInstance($param),
            $params
        );
    }

    private function valueFromInstance(ReflectionParameter $param): mixed
    {
        $name = $param->getName();

        if (method_exists($this, $name)) {
            return $this->{$name}();
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new MagicException('Could not find class member '.$name.' for use as a parameter');
    }

    private function cacheKey(ReflectionMethod $method, array $args): string
    {
        return $this::class.'::'.$method->getName().serialize($args);
    }

    /**
     * Applies a list of transformers to a value and return the result
     *
     * @param  array<callable(mixed): mixed>  $transformers
     */
    private function valueAfterTransforms(mixed $value, array $transformers = []): mixed
    {
        foreach ($transformers as $transformer) {
            $transformer = $this->makeClosure($this, $transformer);
            $value = $transformer($value);
        }

        return $value;
    }
}

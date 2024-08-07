<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Booter;
use AxeBear\Magic\Attributes\MagicProperty;
use AxeBear\Magic\Events\MagicCallEvent;
use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Exceptions\MagicException;
use AxeBear\Magic\Support\Types\TypeCaster;
use Closure;
use InvalidArgumentException;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

trait MagicProperties
{
    use Boots, Magic, MakesClosures, ParsesDocs, Reflections;

    private array $propertyCache = [];

    private array $unboundProperties = [];

    #[Booter]
    protected function bootMagicProperties()
    {
        $this->eachMagicProperty(
            MagicProperty::class,
            fn ($property, $config) => $this->registerMagicProperty($property, $config)
        );

        $this->eachMagicMethod(
            MagicProperty::class,
            fn ($method, $config) => $this->registerMagicMethod($method, $config)
        );

        $this->registerClassProperties();
    }

    /**
     * Gets the raw value of a named property, without any transformations applied to it.
     *
     * @param  string  $name  The name of the property
     * @param  Closure|null  $default  A closure that returns the default value if the property is not set
     * @return mixed The raw value of the property
     */
    public function getRawValue(string $name, ?Closure $default = null): mixed
    {
        if (isset($this->unboundProperties[$name])) {
            return $this->unboundProperties[$name];
        }

        $reflection = new ReflectionClass($this);
        $default ??= fn () => null;
        $prop = $reflection->hasProperty($name) ? $reflection->getProperty($name) : null;

        return $prop ? $prop->getValue($this) : $default();
    }

    protected function registerMagicMethod(ReflectionMethod $method, MagicProperty $config)
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
            $this->onMagicGet($alias, $onGet);
        }
    }

    protected function registerMagicProperty(ReflectionProperty $property, MagicProperty $config)
    {
        if ($property->isPublic()) {
            throw new MagicException('Magic is not available for public properties: '.$property->name);
        }

        $aliases = $config->aliases ? $config->aliases : [$property->getName()];

        foreach ($aliases as $alias) {
            if ($config->readable()) {
                $this->onMagicGet(
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
                $this->onMagicSet(
                    $alias,
                    function (MagicSetEvent $event) use ($property, $config) {
                        $value = $this->valueAfterTransforms(
                            $event->getOutput(fn () => $event->value),
                            $config->onSet
                        );
                        $property->setValue($this, $value);
                        $event->setOutput($value);
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
    protected function registerUnboundProperty(PhpDocTagNode $tag, MagicProperty $config)
    {
        $name = ltrim($tag->value->propertyName, '$');
        $type = $tag->value->type ?? null;

        if ($config->readable()) {
            $this->registerUnboundGetter($name, $config);
        }

        if ($config->writable()) {
            $this->registerUnboundSetter($name, $config, $type);
        }
    }

    protected function registerUnboundGetter(string $name, MagicProperty $config)
    {
        $this->onMagicGet(
            $name,
            function (MagicGetEvent $event) use ($name, $config) {
                $value = $this->valueAfterTransforms($this->getRawValue($name), $config->onGet);
                $event->setOutput($value);
            }
        );
    }

    protected function registerUnboundSetter(string $name, MagicProperty $config, ?IdentifierTypeNode $type)
    {
        $this->onMagicSet(
            $name,
            function (MagicSetEvent $event) use ($name, $type, $config) {
                $newValue = $this->valueAfterTransforms($event->value, $config->onSet);
                if ($type && ! $config->strictTyping) {
                    $newValue = TypeCaster::cast($type->name, $newValue);
                }
                $this->unboundProperties[$name] = $newValue;
                $event->setOutput($newValue);
            }
        );
    }

    protected function registerClassProperties()
    {
        $doc = $this->getDocNode();
        $this->registerTaggedProperties(
            $doc->getTagsByName('@property'),
            new MagicProperty(access: MagicProperty::READ_WRITE)
        );

        $this->registerTaggedProperties(
            $doc->getTagsByName('@property-read'),
            new MagicProperty(access: MagicProperty::READ)
        );

        $this->registerTaggedProperties(
            $doc->getTagsByName('@property-write'),
            new MagicProperty(access: MagicProperty::WRITE)
        );

        $this->registerFluentMethods(
            $doc->getTagsByName('@method')
        );
    }

    /**
     * Register properties from the @property tags.
     *
     * @param  \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode[]  $tags
     */
    protected function registerTaggedProperties(array $tags, MagicProperty $config): void
    {
        $reflection = new ReflectionClass($this);
        foreach ($tags as $tag) {
            $name = ltrim($tag->value->propertyName, '$');

            if ($this->onMagicCall->has($name) || $this->onMagicGet->has($name) || $this->onMagicSet->has($name)) {
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

    protected function registerFluentMethods(array $tags): void
    {
        // Group the tags by method name, since there could be a getter and a setter
        $groups = [];
        foreach ($tags as $tag) {
            $name = $tag->value->methodName;
            $groups[$name] ??= ['set' => false, 'get' => false];

            $isSetter = count($tag->value->parameters) === 1;
            $isGetter = count($tag->value->parameters) === 0;

            $groups[$name] = [
                'set' => $groups[$name]['set'] || $isSetter,
                'get' => $groups[$name]['get'] || $isGetter,
            ];
        }

        foreach ($groups as $name => $methods) {
            $this->onMagicCall(
                $name,
                function (MagicCallEvent $event) use ($methods) {
                    $isSetting = count($event->arguments) === 1;
                    $isGetting = count($event->arguments) === 0;

                    if ($isSetting && ! $methods['set']) {
                        throw new InvalidArgumentException('Method '.$event->name.' is not writable');
                    }

                    if ($isGetting && ! $methods['get']) {
                        throw new InvalidArgumentException('Method '.$event->name.' is not readable');
                    }

                    if ($isSetting && $methods['set']) {
                        $this->__set($event->name, $event->arguments[0]);
                        $event->setOutput($this);
                    } elseif ($isGetting && $methods['get']) {
                        $event->setOutput($this->__get($event->name));
                    } else {
                        throw new InvalidArgumentException('Invalid number of arguments for '.$event->name);
                    }
                }
            );
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

        // Prefer properties first, in case there's a calculated property
        if (property_exists($this, $name) || $this->onMagicGet->handles($name)) {
            return $this->{$name};
        }

        // Then try basic methods
        if (method_exists($this, $name) || $this->onMagicCall->handles($name)) {
            return $this->{$name}();
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

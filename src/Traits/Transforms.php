<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Transform;
use AxeBear\Magic\Events\MagicEvent;
use AxeBear\Magic\Exceptions\MagicException;
use ReflectionProperty;

trait Transforms
{
    use Magic;
    use MakesClosures;

    public function getRaw(string $property): mixed
    {
        return $this->$property;
    }

    protected function bootTransforms(): void
    {
        $this->eachMagicProperty(
            Transform::class,
            fn (ReflectionProperty $property, Transform $transform) => $this->registerTransform($property, $transform)
        );
    }

    protected function registerTransform(ReflectionProperty $property, Transform $transform): void
    {
        if ($property->isPublic()) {
            // __get and __set magic methods aren't called for public properties
            throw new MagicException('Properties with transforms must be protected or private.');
        }

        $propertyName = $property->getName();

        // Add __get handlers
        if ($transform->onGet) {
            $this->onGet($propertyName, function (MagicEvent $event) use ($property, $transform) {
                $value = $property->getValue($this);
                $event->setOutput($this->applyTransforms($value, $transform->onGet));
            });
        }

        // Always return either the transformed or raw value at the end of the transformations
        $this->onGet($propertyName, function (MagicEvent $event) use ($propertyName) {
            if (! $event->hasOutput()) {
                $event->setOutput($this->getRaw($propertyName));
            }
        });

        if ($transform->onSet) {
            // Add __set handlers if provided
            $this->onSet($propertyName, function (MagicEvent $event) use ($property, $transform) {
                $value = $this->applyTransforms($event->input, $transform->onSet);
                $property->setValue($this, $value);
                $event->setOutput($value);
            });
        } else {
            // Otherwise, just set the internal value
            $this->onSet($propertyName, function (MagicEvent $event) use ($property) {
                $property->setValue($this, $event->input);
                $event->setOutput($event->input);
            });
        }
    }

    /**
     * Applies a list of transformers to a value and return the result
     *
     * @param  array<callable(mixed): mixed>  $transformers
     */
    protected function applyTransforms(mixed $value, array $transformers): mixed
    {
        foreach ($transformers as $transformer) {
            $transformer = $this->makeClosure($this, $transformer);
            $value = $transformer($value);
        }

        return $value;
    }
}

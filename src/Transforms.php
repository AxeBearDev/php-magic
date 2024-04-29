<?php

namespace AxeBear\Magic;

use ReflectionClass;
use ReflectionProperty;

trait Transforms
{
    use Magic;

    public function getRaw(string $property): mixed
    {
        return $this->$property;
    }

    protected function bootTransforms(): void
    {
        $transforms = $this->getTransforms();
        foreach ($transforms as [$property, $attributes]) {
            foreach ($attributes as $attribute) {
                $transform = $attribute->newInstance();
                $this->registerTransform($property, $transform);
            }
        }
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
                $event->output(Transform::apply($value, $transform->onGet));
            });
        }

        // Always return either the transformed or raw value at the end of the transformations
        $this->onGet($propertyName, function (MagicEvent $event) use ($propertyName) {
            if (! $event->hasOutput()) {
                $event->output($this->getRaw($propertyName));
            }
        });

        if ($transform->onSet) {
            // Add __set handlers if provided
            $this->onSet($propertyName, function (MagicEvent $event) use ($property, $transform) {
                $value = Transform::apply($event->input, $transform->onSet);
                $property->setValue($this, $value);
                $event->output($value);
            });
        } else {
            // Otherwise, just set the internal value
            $this->onSet($propertyName, function (MagicEvent $event) use ($property) {
                $property->setValue($this, $event->input);
                $event->output($event->input);
            });
        }
    }

    public function getTransforms(): array
    {
        $reflection = new ReflectionClass($this);
        $transforms = [];

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Transform::class);
            if (count($attributes) > 0) {
                $transforms[] = [$property, $attributes];
            }
        }

        return $transforms;
    }
}

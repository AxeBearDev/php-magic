<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\TrackChanges;
use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Exceptions\MagicException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Converts protected properties into tracked, public properties.
 */
trait TracksChanges
{
    use Magic;

    protected array $changes = [];

    public function rollbackChanges(?string $key = null): void
    {
        $changes = $key ? [$key => $this->changes[$key] ?? []] : $this->changes;
        foreach ($changes as $property => $values) {
            if (count($values) < 2) {
                continue;
            }
            [$first] = $values;
            $this->$property = $first;
            $this->changes[$property] = [$first];
        }
    }

    public function getTrackedChanges(?string $key = null): ?array
    {
        return $key ? $this->changes[$key] ?? null : $this->changes;
    }

    public function bootTracksChanges()
    {
        $trackedProperties = $this->getTrackedProperties();
        foreach ($trackedProperties as $property) {
            // Initialize the changes array for the property
            $this->changes[$property->getName()] = [$property->getValue($this)];

            // Watch for changes to the property
            $this->onSet(
                $property->getName(),
                function (MagicSetEvent $event) use ($property) {
                    $this->trackChange($event);
                    $property->setValue($this, $event->getOutput());
                }
            );

            // Make the property publicly accessible
            $this->onGet(
                $property->getName(),
                function (MagicGetEvent $event) use ($property) {
                    $value = $event->hasOutput() ? $event->getOutput() : $property->getValue($this);
                    $event->setOutput($value);
                }
            );
        }
    }

    /**
     * Gets an array of the ReflectionProperty objects that are tracked by the class.
     *
     * @return ReflectionProperty[]
     */
    public function getTrackedProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $classAttributes = $reflection->getAttributes(TrackChanges::class);

        // If the attribute has been added to the class itself, get all properties that match the visibility.
        if ($classAttributes) {
            return $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
        }

        // Otherwise, look for properties that have the attribute.
        $properties = $reflection->getProperties();
        $tracked = array_filter(
            $properties,
            fn ($property) => (bool) $property->getAttributes(TrackChanges::class)
        );

        foreach ($tracked as $prop) {
            if (! $prop->isProtected()) {
                throw new MagicException('Properties with track changes must be protected.');
            }
        }

        return $tracked;
    }

    protected function trackChange(MagicSetEvent $event)
    {
        $name = $event->name;
        $value = $event->hasOutput() ? $event->getOutput() : $event->input;

        // Record the change
        $this->changes[$name][] = $value;

        // Set the property in case it hasn't been set yet
        $event->setOutput($value);
    }
}

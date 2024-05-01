<?php

namespace AxeBear\Magic\Attributes;

use AxeBear\Magic\Magic;
use AxeBear\Magic\MagicEvent;
use AxeBear\Magic\MagicException;
use ReflectionClass;
use ReflectionProperty;

trait TracksChanges
{
    use Magic;

    protected array $changes = [];

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

            // Watch for changes
            $this->onSet(
                $property->getName(),
                function (MagicEvent $event) {
                    $this->trackChange($event);
                }
            );
        }
    }

    /**
     * Gets an array of the ReflectionProperty objects that are tracked by the class.
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

    protected function trackChange(MagicEvent $event)
    {
        $name = $event->name;
        $value = $event->hasOutput() ? $event->output : $event->input;

        // Record the change
        $this->changes[$name][] = $value;

        // Set the property in case it hasn't been set yet
        $event->output($value);
    }
}

<?php

namespace AxeBear\Magic\Traits;

use AxeBear\Magic\Attributes\Booter;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Support\Arr;
use DivisionByZeroError;

trait TracksChanges
{
    use Boots, Magic;

    /**
     * @var array<string, array<MagicSetEvent>>
     */
    protected array $magicSetEvents = [];

    #[Booter]
    protected function bootTrackChanges()
    {
        $this->afterMagicSet('*', fn (MagicSetEvent $event) => $this->trackChange($event));
    }

    protected function trackChange(MagicSetEvent $event)
    {
        $this->magicSetEvents[$event->name][] = $event;
    }

    /**
     * Gets all the changes that have been tracked.
     *
     * @return array<string, array<MagicSetEvent>>
     */
    public function getAllChanges(): array
    {
        return $this->magicSetEvents;
    }

    /**
     * Gets the changes that have been tracked for a given property or all properties.
     *
     * @return array<MagicSetEvent>
     */
    public function getChanges(string $key): array
    {
        return $this->magicSetEvents[$key];
    }

    /**
     * Gets the first value that this property was set to.
     */
    public function getOriginalValue(string $key): mixed
    {
        $changes = $this->getChanges($key);

        if (! $changes) {
            throw new DivisionByZeroError("No changes have been tracked for the property '{$key}'.");
        }

        return $changes[0]->getOutput();
    }

    /**
     * Has any property changed after it was first set?
     */
    public function hasAnyValueChanged(): bool
    {
        $anyChanged = Arr::any(fn ($events) => count($events) > 1);

        return $anyChanged($this->magicSetEvents);
    }

    /**
     * Has the property has changed after it was first set?
     */
    public function hasValueChanged(string $key): bool
    {
        return count($this->getChanges($key)) > 1;
    }

    /**
     * Resets all changes that have been tracked.
     */
    public function resetTrackedChanges(): void
    {
        $this->magicSetEvents = [];
    }
}

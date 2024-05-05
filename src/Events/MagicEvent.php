<?php

namespace AxeBear\Magic\Events;

use Closure;

/**
 * Represents an instance when a magic method is called
 *
 * @template T
 */
class MagicEvent
{
    /** Should propagation of this event should stop */
    public bool $stopped = false;

    public int $createdAt;

    /**
     * Creates a new base magic event
     *
     * @param  string  $name The name of the class member being accessed
     */
    public function __construct(public string $name)
    {
        $this->createdAt = time();
    }

    /**
     * The resulting output of the event
     *
     * @var T
     */
    protected mixed $output;

    /**
     * Stop this event from propagating
     */
    public function stop(): self
    {
        $this->stopped = true;

        return $this;
    }

    public function hasOutput(): bool
    {
        return isset($this->output);
    }

    /**
     * @param  T  $output
     */
    public function setOutput(mixed $output): static
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return T
     */
    public function getOutput(?Closure $defaultValue = null): mixed
    {
        $defaultValue ??= fn () => null;

        return $this->hasOutput() ? $this->output : $defaultValue();
    }
}

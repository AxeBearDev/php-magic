<?php

namespace AxeBear\Magic\Events;

/**
 * Represents an instance when a magic method is called
 *
 * @template T
 */
class MagicEvent
{
    /**
     * The name of the member being accessed
     */
    public string $name = '';

    /** Should propagation of this event should stop */
    public bool $stopped = false;

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
    public function setOutput(mixed $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return T
     */
    public function getOutput(): mixed
    {
        return $this->hasOutput() ? $this->output : null;
    }
}

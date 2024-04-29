<?php

namespace AxeBear\Magic;

/**
 * Represents an instance when a magic method is called
 */
class MagicEvent
{
    /** Should propagation of this event should stop */
    public bool $stopped = false;

    /** The result of the magic event method */
    public mixed $output;

    public function __construct(
      /** The type of magic method called (e.g. __call, __set, etc.) */
      public string $type,

      /** The name param sent to the magic method */
      public string $name,

      /** The arguments sent to the magic method */
      public mixed $input = null,
    ) {
    }

    /**
     * Does this event have an output set?
     */
    public function hasOutput(): bool
    {
        return isset($this->output);
    }

    /**
     * Set the output for this event
     */
    public function output(mixed $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Stop this event from propagating
     */
    public function stop(): self
    {
        $this->stopped = true;

        return $this;
    }
}

<?php

namespace AxeBear\Magic\Support;

use Closure;

class EventRegistry
{
    protected array $handlers = [];

    protected array $afters = [];

    public function on(string $pattern, Closure ...$handlers): static
    {
        $this->handlers[$pattern] = [...($this->handlers[$pattern] ?? []), ...$handlers];

        return $this;
    }

    /**
     * Removes all handlers for the given pattern.
     */
    public function unset(string $pattern): static
    {
        unset($this->handlers[$pattern]);

        return $this;
    }

    /**
     * Does this registry have the pattern registered?
     */
    public function has(string $pattern): bool
    {
        return isset($this->handlers[$pattern]);
    }

    /**
     * Does this registry have any handlers for the given event?
     */
    public function handles(string $event): bool
    {
        return (bool) $this->find($event);
    }

    /**
     * Finds all handlers that match the given event name.
     *
     * @param  string  $event The name of the event to find handlers for.
     * @return array An array of handlers that match the event name.
     */
    public function find(string $event): array
    {
        $finder = Chain::together(
            Arr::where(fn ($handlers, $pattern) => fnmatch($pattern, $event)),
            Arr::flatten(),
            Arr::values()
        );

        return $finder($this->handlers);
    }

    public function __invoke(string $pattern, Closure ...$handlers): static
    {
        return $this->on($pattern, ...$handlers);
    }
}

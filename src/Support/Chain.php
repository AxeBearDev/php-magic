<?php

namespace AxeBear\Magic\Support;

use Closure;
use Throwable;

/**
 * An invocable that chains a series of callables together,
 * passing the result of each to the next.
 */
class Chain
{
    protected Closure $until;

    protected Closure $then;

    protected Closure $each;

    protected Closure $onError;

    protected array $links = [];

    /**
     * Creates a new chain with the given links.
     */
    public static function together(callable ...$links): static
    {
        return (new static)->push(...$links);
    }

    /**
     * Adds new links to the chain
     */
    public function push(callable ...$links): static
    {
        $this->links = array_merge($this->links, $links);

        return $this;
    }

    /**
     * Ensures that each link returns the input parameter it receives.
     * Useful for chaining methods that don't return anything.
     */
    public function carryInput(): static
    {
        return $this->each(function ($link) {
            return function ($input) use ($link) {
                $link($input);

                return $input;
            };
        });
    }

    /**
     * Sets a handler to transform each link in the chain to a new link.
     *
     * @param fn (callable $link) => callable $each The handler to run on each link.
     */
    public function each(Closure $each): static
    {
        $this->each = $each;

        return $this;
    }

    /**
     * Handle errors in the chain without throwing exceptions.
     *
     * @param fn (Throwable $e, mixed $carry, callable $link) $handler The error handler. It should return the value to be passed to the next link.
     */
    public function onError(Closure $handler): static
    {
        $this->onError = $handler;

        return $this;
    }

    /**
     * Sets a condition to stop the chain.
     *
     * @param fn (mixed $carry) => bool $condition The condition to stop the chain.
     */
    public function until(Closure $condition): static
    {
        $this->until = $condition;

        return $this;
    }

    /**
     * Sets a final handler to run after the chain is complete.
     *
     * @param fn (mixed $carry) => mixed $then The final handler.
     */
    public function then(Closure $then): static
    {
        $this->then = $then;

        return $this;
    }

    /**
     * Runs the links in this chain until the condition is met or all links are executed.
     */
    public function __invoke(mixed $input): mixed
    {
        $carry = $input;
        $resolve = fn ($carry) => isset($this->then) ? ($this->then)($carry) : $carry;

        foreach ($this->links as $link) {
            if (isset($this->until) && ($this->until)($carry)) {
                return $resolve($carry);
            }

            if (isset($this->each)) {
                $link = ($this->each)($link, $carry);
            }

            try {
                $carry = $link($carry);
            } catch (Throwable $e) {
                if (isset($this->onError)) {
                    $carry = ($this->onError)($e, $carry, $link);
                } else {
                    throw $e;
                }
            }
        }

        return $resolve($carry);
    }
}

<?php

namespace AxeBear\Magic;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Transform
{
    /**
     * Creates a new instance of the Transform attribute
     *
     * @param  array<callable(mixed): mixed>  $onSet
     * @param  array<callable(mixed): mixed>  $onGet
     */
    public function __construct(
      public array $onSet = [],
      public array $onGet = []
      ) {
    }

    /**
     * Applies a list of transformers to a value and return the result
     *
     * @param  array<callable(mixed): mixed>  $transformers
     */
    public function apply(object $instance, mixed $value, array $transformers): mixed
    {
        foreach ($transformers as $transformer) {
            // Prefer instance methods
            if (is_string($transformer) && method_exists($instance, $transformer)) {
                $transformer = [$instance, $transformer];
            }
            if (! is_callable($transformer)) {
                throw new MagicException('Transformers must be callable.');
            }
            $value = call_user_func($transformer, $value);
        }

        return $value;
    }
}

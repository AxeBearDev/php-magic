<?php

namespace AxeBear\Magic\Events;

/**
 * @template T
 */
class MagicSetEvent extends MagicEvent
{
    /**
     * @param  T  $input
     */
    public function __construct(
      public string $name,
      public mixed $input,
    ) {
    }
}

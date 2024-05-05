<?php

namespace AxeBear\Magic\Events;

/**
 * @template T
 */
class MagicSetEvent extends MagicEvent
{
    /**
     * @param  T  $value
     */
    public function __construct(
      public string $name,
      public mixed $value,
    ) {
        parent::__construct($name);
    }
}

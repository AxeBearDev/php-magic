<?php

namespace AxeBear\Magic\Events;

class MagicGetEvent extends MagicEvent
{
    public function __construct(
      public string $name,
    ) {
        parent::__construct($name);
    }
}

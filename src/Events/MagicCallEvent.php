<?php

namespace AxeBear\Magic\Events;

class MagicCallEvent extends MagicEvent
{
    public function __construct(
      public string $name,
      public array $arguments,
    ) {
        parent::__construct($name);
    }
}

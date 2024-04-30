<?php

namespace AxeBear\Magic\Attributes;

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
}

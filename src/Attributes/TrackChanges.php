<?php

namespace AxeBear\Magic\Attributes;

use Attribute;

/**
 * Tracks the changes made to the protected properties of a class. Apply this either to a class or specific properties.
 */
#[Attribute]
class TrackChanges
{
    public function __construct()
    {
    }
}

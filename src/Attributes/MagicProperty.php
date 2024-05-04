<?php

namespace AxeBear\Magic\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class MagicProperty
{
    const READ = 1;

    const WRITE = 2;

    const READ_WRITE = 3;

    /**
     * Registers a new public property on the class. This can be added to either
     * a property or a method. If added to a method, the method will be used to
     * compute the value of the property. Any arguments that the method expect
     * will be pull from class properties or methods of the same name.
     * If added to a property, the property will be made public and used
     * to store the value.
     *
     * @param  string[]  $aliases The list of public properties to add to the class.
     * @param  fn (MagicSetEvent): void[]  $onSet The list of event handlers to call before the property is set.
     * @param  fn (MagicGetEvent): void[]  $onGet The list of event handlers to call before the property is accessed.
     * @param  bool  $disableCache Disables caching if this is a method.
     * @param  int  $access The access level of the property (READ, WRITE, READ_WRITE).
     */
    public function __construct(
      public array $aliases = [],
      public array $onSet = [],
      public array $onGet = [],
      public bool $disableCache = false,
      public int $access = self::READ_WRITE
    ) {
    }

    public function aliases(string $default): array
    {
        return $this->aliases ?: [$default];
    }

    public function readable(): bool
    {
        return $this->access & self::READ;
    }

    public function writable(): bool
    {
        return $this->access & self::WRITE;
    }

    public function readonly(): bool
    {
        return $this->access === self::READ;
    }

    public function writeonly(): bool
    {
        return $this->access === self::WRITE;
    }

    public function readwrite(): bool
    {
        return $this->access === self::READ_WRITE;
    }
}

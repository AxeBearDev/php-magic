# PHP Magic

This PHP package provides utilities for adding magic properties and methods to your classes using custom attributes and docblocks. Highlights include:

- Automagic properties, created with the `@property` tag.
- Automagic cached, calculated properties using the `@property-read` tag and a method with the same name.
- Automagic fluent methods the `@method` tag.
- Easier overloaded methods. Use `@method` and the `#[Overloaded]` attribute to split out the logic for overloaded methods into separate functions. This package will call the correct one based on the arguments.
- Transformed properties, on set or get, with the `#[MagicProperty]` attribute.
- Full access to add more custom handlers using the `Magic` trait.

Check it out. It's magic!

```php
use AxeBear\Magic\Traits\MagicProperties;
use AxeBear\Magic\Traits\OverloadedMethods;
use AxeBear\Magic\Attributes\Overloaded;

/**
 * This example class shows how class comments
 * can be used to define magic properties and methods.
 *
 * @property string $name
 * @method self name(string $name)
 *
 * @property int $count
 * @method self count(int $count)
 *
 * @property string $repeatedName
 *
 * @method void update(...$args)
 */
class Model {
  use MagicProperties;
  use OverloadedMethods;

  #[Overloaded('update')]
  public function updateFromArray(array $data): void {
    $this->name ??= $data['name'] ?? null;
    $this->count ??= $data['count'] ?? null;
  }

  #[Overloaded('update')]
  public function updateFromValues(string $name, int $count): void {
    $this->name = $name;
    $this->count = $count;
  }

  public function repeatedName(string $name, int $count): string {
    return str_repeat($name, $count);
  }
}

$model = new Model();
$model->name('Axe Bear')->count(1);
$model->update(['name' => 'Axe', 'count' => 2]);
$model->update('Bear', 2);
echo $model->name; // Bear
echo $model->count; // 2
echo $model->repeatedName; // BearBear

```

## Installation

```bash
composer require axebeardev/php-magic
```

## Scripts

- `composer test`: Test with Pest
- `composer cli`: Open a Pysch shell with the package loaded

---

# Magic

This base trait is a registry for all of the handlers to call when a magic method is needed. The other traits in this package use this one at their core to provide the magic functionality, but it's also available for you to use directly.


**Important Hints**

The [visibility](https://www.php.net/manual/en/language.oop5.visibility.php) of the properties and methods that you use with the `Magic` trait is important. The class members being [overloaded](https://www.php.net/manual/en/language.oop5.overloading.php) should be inaccessible, either `protected` or `private`, so that the magic methods can be called.

## Events

When a magic method is called, the `Magic` trait will generate a `MagicEvent` instance and pass it to any registered handlers that match the event name (using [fnmatch](https://www.php.net/manual/en/function.fnmatch.php)).

The base `MagicEvent` instanced includes the following properties:

- `name`: The name of the class member being called.
- `stopped`: A boolean value that can be set to `true` to stop the event from being processed by any further handlers.

This class also provides the ability to set an output value that will be returned by the magic method. The `Magic` trait will return this value when processing the magic method. The output can be manipulated by any of the handlers that are registered for the event in turn, which means you can pipe the output value through multiple functions.

- `setOutput(mixed $value): static` Sets the output value for the event.
- `hasOutput(): bool` Checks if the event has an output value set. (This is important because `null` is a valid output value.)
- `getOutput(?Closure $defaultValue = null): mixed` Gets the output value for the event.

### [\_\_get](https://www.php.net/manual/en/language.oop5.overloading.php#object.get)

- Listener: `onGet(string $name, Closure ...$handlers): static`
- Event: `MagicGetEvent`

```php
public __get(string $name): mixed
```

To hook into this event, register one or more handlers using the `$this->onGet($pattern, Closure ...$handlers)` method. The closure should expect a `MagicGetEvent` instance as its parameter.

### [\_\_set](https://www.php.net/manual/en/language.oop5.overloading.php#object.set)

- Listener: `onSet(string $name, Closure ...$handlers): static`
- Event: `MagicSetEvent`

```php
public __set(string $name, mixed $value): void
```

To hook into this event, register one or more handlers using the `$this->onSet($pattern, Closure ...$handlers)` method. The closure should expect a `MagicSetEvent` instance as its parameter. This event includes an additional `value` property that contains the value being set.

### [\_\_call](https://www.php.net/manual/en/language.oop5.overloading.php#object.call)

- Listener: `onCall(string $name, Closure ...$handlers): static`
- Event: `MagicCallEvent`

```php
public __call(string $name, array $arguments): mixed
```

To hook into this event, register one or more handlers using the `$this->onCall($pattern, Closure ...$handlers)` method. The closure should expect a `MagicCallEvent` instance as its parameter. This event includes an additional `arguments` property that contains the arguments being passed to the method.

### [\_\_callStatic](https://www.php.net/manual/en/language.oop5.overloading.php#object.callstatic)

- Listener: `onCallStatic(string $name, Closure ...$handlers): void`
- Event: `MagicCallStaticEvent`

```php
public __callStatic(string $name, array $arguments): mixed
```

To hook into this event, register one or more handlers using the `$this->onCallStatic($pattern, Closure ...$handlers)` method. The closure should expect a `MagicCallStaticEvent` instance as its parameter. This event includes an additional `arguments` property that contains the arguments being passed to the method.

---

# MagicProperties

This trait inspects your class documentation for `@property`, `@property-read`, and `@property-write` tags and adds the corresponding magic methods to your class so that those properties work. You can optionally add configuration to any of the properties with the `#[MagicProperty]` attribute.

## Basic Usage

At its simplest when you include `@property` tags in your class documentation, the `MagicProperties` trait will add a getter and setter for the property.


If there the class includes a protected or private property of the same name, it will be used as the backing storage for the property. If there is not property with the name, the values will be stored in an `unboundProperties` array defined in the trait.

In either case, you can use the `getRawValue` method to get the raw value of the property, bypassing any transformations that may be applied. (See the section on transforming values for more information.)

```php
/**
 * @property string $name
 * @property int $count
 */
class Model {
  use MagicProperties;
}

$model = new Model();

$model->name = 'ernst';
echo $model->name; // ernst

$model->count = 5;
echo $model->count; // 5

// Simple type coercion is applied based on the type hint in the property tag.
$model->count = '6';
echo $model->count; // 6 
```

## Read-Only and Write-Only Properties

You can also define read-only and write-only properties with the `@property-read` and `@property-write` tags. These can't be unbound. They'll need a backing property in your class. Otherwise a readonly property won't have an initial value, and a write-only property won't have a place to store the value.

```php
/**
 * @property-read string $defaultName
 * @property-write string $newName
 */
class Model {
  use MagicProperties;

  protected string $defaultName = 'leonora';

  protected string $newName;
}

$model = new Model();
echo $model->defaultName; // leonora
$model->newName = 'ernst';
```

## Calculated Properties

You can also define calculated properties by adding a `@property-read` tag to your class documentation and defining a protected or private method with the same name as the property.

If the calculation has any dependencies on other class values, you should add those as parameters to the method. Use the same name as the class members. Output of calculated properties are cached, and any parameters included in the method signature will be used to calculate the cache.

```php
/**
 * @property string $name
 * @property int $count
 * @property-read string $repeatedName
 */
class Model {
  use MagicProperties;

  protected string $name;

  protected int $count;

  protected function repeatedName(string $name, int $count): string
  {
    return str_repeat($name, $count);
  }
}

$model = new Model();
$model->name = 'ernst';
$model->count = 3;
echo $model->repeatedName; // ernsternsternst
```

## Transforming Values

You can also customize how a property is set or retrieved by adding a `#[MagicProperty]` attribute to the property. The `#[MagicProperty]` attribute accepts `onGet` and `onSet` parameters that allow modifying the value before setting it.

Both `onSet` and `onGet` accept an array of callables that will be called in the order they are defined. The callables should accept the value as the first parameter and return the modified value. You may use either built-in PHP functions or custom class methods that are defined on the class.

```php
/**
 * @property string $message
 */
class Model {
  use MagicProperties;

  #[MagicProperty(onSet: ['encode'], onGet: ['decode'])]
  protected string $message;

  protected function encode(string $value): string
  {
      return base64_encode($value);
  }

  protected function decode(string $value): string
  {
      return base64_decode($value);
  }
}
$model = new Model();
$model->message = 'ernst';
echo $model->message; // ernst
echo $model->getRawValue('message'); // ZXJuc3Q=
```

## Fluent Getters and Setters

In addition to mapping properties, you can also create magic getter and setter methods using the `@method` tag in your class documentation. This is useful when you want to provide a fluent interface for your class. The `MapDocBlock` trait will automatically add the magic methods to your class when it sees the `@method` tag with either zero or one parameters.

If the `@method` tag includes one parameter, the `MapDocBlock` trait will add a setter method. If the `@method` tag includes zero parameters, the `MapDocBlock` trait will add a getter method.

```php
/**
 * @method string name()
 * @method self name(string $name)
 */
class Model {
  use MagicProperties;
}

$model = new Model();
$model->name('ernst');
echo $model->name(); // ernst
```

---

# Overloaded Methods

PHP doesn't yet offer clean syntax for overloading methods. With the `#[Overloaded]` attribute and the `OverloadedMethods` trait, you can split out the logic for overloaded methods into separate methods that are called based on the type of the arguments passed to the method.

Instead of:

```php
class Model {
  public function find(...$args) {
    if (count($args) === 1 && is_int($args[0])) {
      return $this->findById($args[0]);
    }
    if (count($args) === 1 && is_string($args[0])) {
      return $this->findBySlug($args[0]);
    }
    if (count($args) === 2 && is_string($args[0]) && is_int($args[1])) {
      return $this->findBySlugAndId($args[0], $args[1]);
    }

    throw new InvalidArgumentException('Invalid arguments');
  }

  protected function findById(int $id) {
    return "id: $id";
  }

  protected function findBySlug(string $slug) {
    return "slug: $slug";
  }

  protected function findBySlugAndId(string $slug, int $id) {
    return "slug: $slug, id: $id";
  }
}
```

You can do this:

```php

use AxeBear\Magic\Attributes\Overloaded;
use AxeBear\Magic\Traits\OverloadedMethods;

/**
 * @method string find(...$args)
 */
class Model {
  use OverloadedMethods;

  #[Overloaded('find')]
  protected function findById(int $id) {
    return "id: $id";
  }

  #[Overloaded('find')]
  protected function findBySlug(string $slug) {
    return "slug: $slug";
  }

  #[Overloaded]('find')
  protected function findBySlugAndId(string $slug, int $id) {
    return "slug: $slug, id: $id";
  }
}
```

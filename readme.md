# PHP Magic

This PHP package provides utilities for adding magic properties and methods to your classes using custom attributes and docblocks.

Check it out. It's magic!

```php
use AxeBear\Magic\Traits\MagicDocBlock;

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
 */
class Model {
  use MagicDocBlock;

  public function repeatedName(string $name, int $count): string {
    return str_repeat($name, $count);
  }
}

$model = new Model();
$model->name('Axe Bear')->count(1);
echo $model->name; // Axe Bear
echo $model->count; // 1
echo $model->repeatedName; // Axe Bear

```

## Installation

```bash
composer require spleenboy/php-magic
```

## Scripts
  
- `composer test`: Test with Pest
- `composer cli`: Open a Pysch shell with the package loaded

## What's in the Box?

This package provides powerful utilities for reusing code and adding functionality to your classes. The `Magic` in this package takes advantange of [PHP's magic methods](https://www.php.net/manual/en/language.oop5.magic.php) to provide a composable way to add functionality to your classes via [PHP DocBlocks](https://docs.phpdoc.org/) and [custom attributes](https://www.php.net/manual/en/class.attribute).

This package uses magic methods to add members that aren't exclicitly defined in your classes.

---

# Magic

This base trait is a registry for all of the handlers to call when a magic method is needed.

## Events

When a magic method is called, the `Magic` trait will generate a `MagicEvent` instance and pass it to any registered handles that match the event name. The event name is the name of the class member being called when any of these magic methods are called.

The base `MagicEvent` instanced includes the following properties:

- `name`: The name of the class member being called.
- `stopped`: A boolean value that can be set to `true` to stop the event from being processed by any further handlers.

This class also provides the ability to set an output value that will be returned by the magic method. The `Magic` trait will return this value when processing the magic method. The output can be manipulated by any of the handlers that are registered for the event in turn, which means you can pipe the output value through multiple functions.

- `setOutput(mixed $value): static` Sets the output value for the event.
- `hasOutput(): bool` Checks if the event has an output value set. (This is important because `null` is a valid output value.)
- `getOutput(?Closure $defaultValue = null): mixed` Gets the output value for the event.


### [__get](https://www.php.net/manual/en/language.oop5.overloading.php#object.get)

Listener: `onGet(string $name, Closure ...$handlers): static`
Event: `MagicGetEvent`

```php
public __get(string $name): mixed
```

To hook into this event, register one or more handlers using the `$this->onGet($pattern, Closure ...$handlers)` method. The closure should expect a `MagicGetEvent` instance as its parameter.


### [__set](https://www.php.net/manual/en/language.oop5.overloading.php#object.set)

Listener: `onSet(string $name, Closure ...$handlers): static`
Event: `MagicSetEvent`

```php
public __set(string $name, mixed $value): void
```

To hook into this event, register one or more handlers using the `$this->onSet($pattern, Closure ...$handlers)` method. The closure should expect a `MagicSetEvent` instance as its parameter. This event includes an additional `value` property that contains the value being set.


### [__call](https://www.php.net/manual/en/language.oop5.overloading.php#object.call)

Listener: `onCall(string $name, Closure ...$handlers): static`
Event: `MagicCallEvent`

```php
public __call(string $name, array $arguments): mixed
```

To hook into this event, register one or more handlers using the `$this->onCall($pattern, Closure ...$handlers)` method. The closure should expect a `MagicCallEvent` instance as its parameter. This event includes an additional `arguments` property that contains the arguments being passed to the method.


### [__callStatic](https://www.php.net/manual/en/language.oop5.overloading.php#object.callstatic)

Listener: `onCallStatic(string $name, Closure ...$handlers): void`
Event: `MagicCallStaticEvent`

```php
public __callStatic(string $name, array $arguments): mixed
```

To hook into this event, register one or more handlers using the `$this->onCallStatic($pattern, Closure ...$handlers)` method. The closure should expect a `MagicCallStaticEvent` instance as its parameter. This event includes an additional `arguments` property that contains the arguments being passed to the method.

---

# MagicDocBlock

This trait inspects your class documentation for `@property`, `@property-read`, and `@property-write` tags and adds the corresponding magic methods to your class so that those properties work. You can optionally add configuration to any of the properties with the `#[MagicProperty]` attribute.

## Basic Usage

At its simplest when you include `@property` tags in your class documentation, the `MapDocBlock` trait will add a getter and setter for the property.

```php
/**
 * @property string $name
 * @property int $count
 */
class Model {
  use MagicDocBlock;
}

$model = new Model();

$model->name = 'ernst';
echo $model->name; // ernst

$model->count = 5;
echo $model->count; // 5
```

## Read-Only and Write-Only Properties

You can also define read-only and write-only properties with the `@property-read` and `@property-write` tags. These variables will need a backing property in your class since otherwise. Otherwise, a readonly property won't have an initial value, and a write-only property won't have a place to store the value.

```php
/**
 * @property-read string $defaultName
 * @property-write string $newName
 */
class Model {
  use MagicDocBlock;

  protected string $defaultName = 'leonora';

  protected string $newName;
}

$model = new Model();
echo $model->defaultName; // leonora
$model->newName = 'ernst';
```

## Calculated Properties

You can also define calculated properties by adding a `@property-read` tag to your class documentation and defining a method with the same name as the property.

If the calculation has any dependencies on other class values, you should add those as parameters to the method. Use the same name as the class members. Output of calculated properties are cached, and any parameters included in the method signature will be used to calculate the cache.

```php
/**
 * @property string $name
 * @property int $count
 * @property-read string $repeatedName
 */
class Model {
  use MagicDocBlock;

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
  use MagicDocBlock;

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
  use MagicDocBlock;
}

$model = new Model();
$model->name('ernst');
echo $model->name(); // ernst
```
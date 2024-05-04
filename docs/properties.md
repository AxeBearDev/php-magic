# Properties

Inspects your class documentation for `@property`, `@property-read`, and `@property-write` tags and adds the corresponding magic methods to your class so that those properties work. You can optionally add configuration to any of the properties with the `#[Property]` attribute.

## Basic Usage

At its simplest when you include `@property` tags in your class documentation, the `Properties` trait will add a getter and setter for the property.

```php
/**
 * @property string $name
 * @property int $count
 */
class Model {
  use Properties;
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
  use Properties;

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
  use Properties;

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

You can also customize how a property is set or retrieved by adding a `#[Property]` attribute to the property. This attribute takes a `transform` parameter that is a callable that takes the value and returns the transformed value.

```php
use AxeBear\Magic\Traits\Properties;

/**
 * @property string $name
 * @property int $count
 * @property-read string $defaultName
 * @property-write string $newName
 */
class Model {
  use Properties;

  protected string $name;

  protected int $count;

  protected string $defaultName = 'leonora';

  protected string $newName;

}
$model = new Model();
echo $model->name; // Leonora
echo $model->name('ernst'); // ernst
```
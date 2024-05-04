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

You can also customize how a property is set or retrieved by adding a `#[Property]` attribute to the property. The `#[Property]` attribute accepts `onGet` and `onSet` parameters that allow modifying the value before setting it.

Both `onSet` and `onGet` accept an array of callables that will be called in the order they are defined. The callables should accept the value as the first parameter and return the modified value. You may use either built-in PHP functions or custom class methods that are defined on the class.

```php
/**
 * @property string $message
 */
class Model {
  use Properties;

  #[Property(onSet: ['encode'], onGet: ['decode'])]
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

In addition to mapping properties, you can also create magic getter and setter methods using the `@method` tag. This is useful when you want to provide a fluent interface for your class. The `Properties` trait will automatically add the magic methods to your class when it sees the `@method` tag with either zero or one parameters.

If the `@method` tag includes one parameter, the `Properties` trait will add a setter method. If the `@method` tag includes zero parameters, the `Properties` trait will add a getter method.

```php
/**
 * @method string name()
 * @method self name(string $name)
 */
class Model {
  use Properties;
}

$model = new Model();
$model->name('ernst');
echo $model->name(); // ernst
```
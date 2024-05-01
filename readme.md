# PHP Magic

This PHP package uses magic methods to provide a composable way to add functionality to your classes via custom attributes.

## Installation

```bash
composer require spleenboy/php-magic
```

## Scripts
  
- `composer test`: Test with Pest
- `composer cli`: Open a Pysch shell with the package loaded

## Attributes

### Transform

The `#[Transform]` attribute and the `Transforms` trait work together to apply changes to class properties when they are set or retrieved.

```php
class Model {
  use Transforms;

  #[Transform(onSet: ['ucfirst', 'trim'])]
  protected string $name;

  #[Transform(onGet: ['strtolower'])]
  protected string $email;

  #[Transform(onSet: ['encode'], onGet: ['decode'])]
  protected string $value;

  public function encode(string $value): string {
    return base64_encode($value);
  }

  public function decode(string $value): string {
    return base64_decode($value);
  }
}

$model = new Model();
$model->name = ' leonora ';
echo $model->name; // Leonora
// This transform is applied on set, so the underlying value matches the transformed value.
echo $model->getRaw('name'); // 'Leonora'

$model->email = 'Leonora@example.COM';
echo $model->email; // leonora@example.com
// This transform applies on get, so the underlying value is unchanged from what was set
echo $model->getRaw('email'); // 'Leonora@example.COM' 

$model->value = 'Hello, World!';
echo $model->value; // 'Hello, World!'
// This transform applies on both set and get, so the underlying value is transformed
echo $model->getRaw('value') // SGVsbG8sIFdvcmxkIQ==
```

### Getter

The `#[Getter]` attribute and the `Getters` trait will convert class methods into magic properties.

```php
class Model {
  use Getters;

  #[Getter]
  public function name(): string {
    return 'Leonora';
  }
}

$model = new Model();
echo $model->name; // Leonora
```

### Compute

This is a fancier version of the `#[Getter]` attribute and trait that supports arguments in the getter method. If `useCache` is set to `true`, the method and argument values will be used to cache the result of the computation and return it on subsequent calls.

```php
class Model {
  use Computes;

  public string $defaultName = 'leonora';

  #[Compute(useCache: true)]
  public function name(string $defaultName): string {
    return ucfirst($defaultName);
  }
}
$model = new Model();
echo $model->name; // Leonora
echo $model->name('ernst'); // ernst
```

### Track Changes

The `#[TrackChanges]` attribute and/or the `TrackChanges` trait will track changes to protected class properties.

You can either track changes to specific properties or to all of the protected properties in a class.

```php
class Model {
  use TrackChanges;

  #[TrackChanges]
  protected string $name;
}

$model = new Model();
$model->name = 'Leonora';
$model->name = 'Leonora Carrington';
$model->getTrackedChanges(); // [ 'name' => ['Leonora', 'Leonora Carrington'] ]
$model->rollbackChanges('name');
```php

Tracking on the class level may give you more than you expect, since all protected properties will be tracked and made public, including inherited properties.
  
```php
#[TrackChages]
class Model {
  use TrackChanges;

  protected string $name;
}

$model = new Model();
$model->name = 'Leonora';
$model->name = 'Leonora Carrington';
```
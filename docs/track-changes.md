# Track Changes

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
# Getters

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
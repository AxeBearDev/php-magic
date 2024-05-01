# Computes

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
# Properties

Inspects your class documentation for `@property`, `@property-read`, and `@property-write` tags and adds the corresponding magic methods to your class so that those properties work. You can optionally add configuration to any of the properties with the `#[Property]` attribute.


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
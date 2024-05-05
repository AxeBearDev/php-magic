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
 * @method string name()
 * 
 * @property int $count
 * @method self count(int $count)
 * @method int count()
 * 
 * @property string $repeatedName
 * @property-read string $readonlyValue
 * @property-write string $writeOnlyValue
 */
class Model {
  use MagicDocBlock;

  protected string $readonlyValue = 'Hello, World!';

  public function repeatedName(string $name, int $count): string {
    return str_repeat($name, $count);
  }
}

$model = new Model();
$model->name('Axe Bear')->count(1);
echo $model->name(); // Axe Bear
echo $model->count(); // 1
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

### [Magic Properties](docs/properties.md)

The `Properties` trait adds support in your classes to define magic properties and methods in the docblock for your class. You can further customize these using the `#[MagicProperty]` attribute.

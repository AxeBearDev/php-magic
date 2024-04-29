# PHP Magic

This PHP package uses magic methods to provide a composable way to add functionality to your classes via custom attributes.

## Installation

```bash
composer require spleenboy/php-magic
```

## Attributes

### Transform

The `#[Transform]` attribute and the `Transforms` trait work together to apply changes to class properties when they are set or retrieved.

Let's start with a comprehensive example:

```php
class Model {
  use Transforms;

  #[Transform(onSet: ['ucfirst'])]
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
$model->name = 'galadriel';
echo $model->name; // Galadriel
echo $model->getRaw('name') // galadriel

$model->email = 'Galadriel@example.COM';
echo $model->email; // galadriel@example.com
echo $model->getRaw('email') // 'Galadriel@example.COM';

$model->value = 'Hello, World!';
echo $model->value; // 'Hello, World!'
echo $model->getRaw('value') // SGVsbG8sIFdvcmxkIQ==
```
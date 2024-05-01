# Fluency

This trait and attribute combination will add setter methods for class properties of the specified visibility. This is useful for chaining together multiple setter calls.

```php
#[Fluent(visibility: ReflectionProperty::IS_PUBLIC|ReflectionProperty::IS_PROTECTED)]
class Model {
    use Fluency;

    public string $name;
    protected int $age;

    public function getAge(): int {
        return $this->age;
    }
}

$model = new Model();
$model->name('Leonora')->age(30);
$model->name; // Leonora
$model->getAge(); // 30
$model->age; // throw an error since age is protected
```
# Fluency

This [trait](../src/Traits/Fluency.php) and [attribute](../src/Attributes/Fluent.php) combination will add setter methods for class properties of the specified visibility. This is useful for chaining together multiple setter calls. The `Fluent` trait is optional, but only public properties are considered by default. You can change this by adding the `Fluent` attribute to a class that uses the `Fluency` trait.

```php
#[Fluent(visibility: ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED)]
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
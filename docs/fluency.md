# Fluency

This trait and attribute combination will add setter and getter methods for class properties of the specified visibility. This is useful for chaining together multiple setter calls.

```php
#[Fluent(visibility: ReflectionProperty::IS_PUBLIC|ReflectionProperty::IS_PROTECTED)]
class Model {
    use Fluency;
    
    public string $name;
    protected int $age;
}

$model = new Model();
$model->name('John')->age(30);
```
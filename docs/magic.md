# Magic

```php
class User
{
  use Magic;
  use Transforms;

  #[Transform(onSet: ['ucfirst'])]
  public string $name;
}

$user = new User();
$user->name = 'john';
echo $user->name; // John
```
# Magic

## Transforms

Combine the `#[Transform]` attribute and the `Transforms` trait to apply changes to class properties when they are set or retrieved.

```php
class User
{
  use Transforms;

  #[Transform(onSet: ['ucfirst'])]
  protected string $name;

  #[Transform(onGet: ['strtolower'])]
  protected string $email;
}

$user = new User();
$user->name = 'galadriel';
echo $user->name; // Galadriel
echo $user->getRaw('name') // Galadriel

$user->email = 'GALADRIEL@example.com';
echo $user->email; // galadriel@example.com
echo $user->getRaw('email') // GALADRIEL@example.com
```
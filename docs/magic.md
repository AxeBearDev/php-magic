# Magic

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
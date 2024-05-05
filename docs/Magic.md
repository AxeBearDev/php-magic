# Magic

This base trait is a registry for all of the handlers to call when a magic method is needed.

## Events

When a magic method is called, the `Magic` trait will generate a `MagicEvent` instance and pass it to any registered handles that match the event name. The event name is the name of the class member being called when any of these magic methods are called.

The base `MagicEvent` instanced includes the following properties:

- `name`: The name of the class member being called.
- `stopped`: A boolean value that can be set to `true` to stop the event from being processed by any further handlers.

This class also provides the ability to set an output value that will be returned by the magic method. The `Magic` trait will return this value when processing the magic method. The output can be manipulated by any of the handlers that are registered for the event in turn, which means you can pipe the output value through multiple functions.

- `setOutput(mixed $value): static` Sets the output value for the event.
- `hasOutput(): bool` Checks if the event has an output value set. (This is important because `null` is a valid output value.)
- `getOutput(?Closure $defaultValue = null): mixed` Gets the output value for the event.


### [__get](https://www.php.net/manual/en/language.oop5.overloading.php#object.get)

Listener: `onGet(string $name, Closure ...$handlers): static`
Event: `MagicGetEvent`

```php
public __get(string $name): mixed
```

To hook into this event, register one or more handlers using the `$this->onGet($pattern, Closure ...$handlers)` method. The closure should expect a `MagicGetEvent` instance as its parameter.


### [__set](https://www.php.net/manual/en/language.oop5.overloading.php#object.set)

Listener: `onSet(string $name, Closure ...$handlers): static`
Event: `MagicSetEvent`

```php
public __set(string $name, mixed $value): void
```

To hook into this event, register one or more handlers using the `$this->onSet($pattern, Closure ...$handlers)` method. The closure should expect a `MagicSetEvent` instance as its parameter. This event includes an additional `value` property that contains the value being set.


### [__call](https://www.php.net/manual/en/language.oop5.overloading.php#object.call)

Listener: `onCall(string $name, Closure ...$handlers): static`
Event: `MagicCallEvent`

```php
public __call(string $name, array $arguments): mixed
```

To hook into this event, register one or more handlers using the `$this->onCall($pattern, Closure ...$handlers)` method. The closure should expect a `MagicCallEvent` instance as its parameter. This event includes an additional `arguments` property that contains the arguments being passed to the method.


### [__callStatic](https://www.php.net/manual/en/language.oop5.overloading.php#object.callstatic)

Listener: `onCallStatic(string $name, Closure ...$handlers): void`
Event: `MagicCallStaticEvent`

```php
public __callStatic(string $name, array $arguments): mixed
```

To hook into this event, register one or more handlers using the `$this->onCallStatic($pattern, Closure ...$handlers)` method. The closure should expect a `MagicCallStaticEvent` instance as its parameter. This event includes an additional `arguments` property that contains the arguments being passed to the method.
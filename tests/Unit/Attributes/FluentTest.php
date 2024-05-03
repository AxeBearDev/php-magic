<?php

use AxeBear\Magic\Attributes\Fluent;
use AxeBear\Magic\Exceptions\MagicException;
use AxeBear\Magic\Traits\Fluency;

class FluentUser
{
    use Fluency;

    public string $firstName = 'Jean';

    public int $count = 0;

    protected string $email = '';
}

#[Fluent(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED)]
class ProtectedFluentUser
{
    use Fluency;

    public string $firstName = 'Jean';

    protected string $email = '';

    private int $count = 0;

    public function getEmail(): string
    {
        return $this->email;
    }
}

describe('#[Fluent]', function () {
    test('default to public', function () {
        $user = new FluentUser();
        $user->firstName('Jane')->count(1);

        expect($user->firstName)->toBe('Jane');
        expect($user->count)->toBe(1);

        $this->expectException(MagicException::class);
        $user->email('failure!');

        $this->expectException(TypeError::class);
        $user->count('string'); // Bad type.
    });

    test('protected and public', function () {
        $user = new ProtectedFluentUser();
        expect($user->getFluencyVisibility())->toBe(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $user->firstName('Jane')->email('jane@jean.pants');
        expect($user->firstName)->toBe('Jane');
        expect($user->getEmail())->toBe('jane@jean.pants');

        $this->expectException(MagicException::class);
        $user->count(1);
    });

    test('getters', function () {
        $user = new ProtectedFluentUser();
        $user->firstName('Jane')->email('jane@jean.pants');
        expect($user->firstName())->toBe($user->firstName);
        expect($user->email())->toBe($user->getEmail());
    });

    test('invalid arguments', function () {
        $user = new ProtectedFluentUser();
        $this->expectException(InvalidArgumentException::class);
        $user->firstName('Jane', 'extra');
    });
});

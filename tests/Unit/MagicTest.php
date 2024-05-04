<?php

use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Traits\Magic;

class MagicTestModel implements ArrayAccess
{
    use Magic;

    protected array $data = [];

    protected function traitsBooted(): void
    {
        $this->onGet('*', fn (MagicGetEvent $e) => $e->setOutput($this->data[$e->name] ?? null));
        $this->onSet('*', fn (MagicSetEvent $e) => $this->data[$e->name] = $e->value);
    }
}

describe('Magic', function () {
    $values = [
        'name' => 'Ace',
        'age' => 25,
        'id' => 1,
    ];

    test('getters and setters', function () use ($values) {
        $model = new MagicTestModel();
        foreach ($values as $key => $value) {
            $model->$key = $value;
            expect($model->$key)->toBe($value);
        }
    });

    test('ArrayAccess', function () use ($values) {
        $model = new MagicTestModel();
        foreach ($values as $key => $value) {
            $model[$key] = $value;
            expect($model[$key])->toBe($value);
        }
    });
});

<?php

use AxeBear\Magic\Events\MagicGetEvent;
use AxeBear\Magic\Events\MagicSetEvent;
use AxeBear\Magic\Traits\Magic;

class MagicTestModel implements ArrayAccess
{
    use Magic;

    protected array $history = [];

    protected array $data = [];

    public function __construct()
    {
        $this->onGet('*', fn (MagicGetEvent $e) => $e->setOutput($this->data[$e->name] ?? null));
        $this->onSet('*', fn (MagicSetEvent $e) => $this->data[$e->name] = $e->value);
        $this->afterSet('*', fn (MagicSetEvent $e) => $this->history[] = $e);
    }

    public function getHistory(): array
    {
        return $this->history;
    }
}

test('sequencing', function () {
    $model = new MagicTestModel();
    $model->name = 'Ace';
    $model->age = 25;
    $model->id = 1;

    $history = $model->getHistory();
    expect($history)->toHaveCount(3);
    expect($history[0]->name)->toBe('name');
    expect($history[0]->value)->toBe('Ace');
    expect($history[1]->name)->toBe('age');
    expect($history[1]->value)->toBe(25);
    expect($history[2]->name)->toBe('id');
    expect($history[2]->value)->toBe(1);
});

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

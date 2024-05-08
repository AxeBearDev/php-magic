<?php

use AxeBear\Magic\Attributes\Overloaded;
use AxeBear\Magic\Traits\OverloadedMethods;

class OverloadedModelItem
{
    public function __construct(
      public string $name,
      public int $age,
      public float $value,
    ) {
    }
}

/**
 * @method array find(...$args)
 */
class OverloadedModel
{
    use OverloadedMethods;

    protected array $items = [];

    public function __construct()
    {
        $this->bootTraits();
        $this->items = [
            new OverloadedModelItem('Blue', 25, 1.2),
            new OverloadedModelItem('Red', 30, 1.5),
            new OverloadedModelItem('Green', 35, 1.8),
        ];
    }

    #[Overloaded('find')]
    public function findByAge(int $age, ?OverloadedModelItem $default = null)
    {
        return $this->findByKey('age', $age, $default);
    }

    #[Overloaded('find')]
    public function findByName(string $name, ?OverloadedModelItem $default = null)
    {
        return $this->findByKey('name', $name, $default);
    }

    #[Overloaded('find')]
    public function findByKey(string $key, int|string $value, ?OverloadedModelItem $default = null)
    {
        foreach ($this->items as $item) {
            if ($item->$key === $value) {
                return $item;
            }
        }

        return $default;
    }

    #[Overloaded('find')]
    public function findByAll(string $name, int $age, float $value, ?OverloadedModelItem $default = null)
    {
        foreach ($this->items as $item) {
            if ($item->name === $name && $item->age === $age && $item->value === $value) {
                return $item;
            }
        }

        return $default;
    }

    #[Overloaded('find')]
    public function findByAllDuplicate(string $name, int $age, float $value, ?OverloadedModelItem $default = null)
    {
        return $this->findByAll($name, $age, $value, $default);
    }
}

describe('OverloadedMethods', function () {
    test('find exists', function () {
        $model = new OverloadedModel();
        expect($model->hasMagicCaller('find'))->toBeTrue();
    });

    test('find by age', function () {
        $model = new OverloadedModel();
        $result = $model->find(25);
        $expected = $model->findByAge(25);
        expect($result)->toBe($expected);
    });

    test('find by age with default', function () {
        $model = new OverloadedModel();
        $defaultValue = new OverloadedModelItem('Default', 0, 0.0);
        $result = $model->find(1, $defaultValue);
        expect($result)->toBe($defaultValue);
    });

    test('find by name', function () {
        $model = new OverloadedModel();
        $result = $model->find('Blue');
        $expected = $model->findByName('Blue');
        expect($result)->toBe($expected);
    });

    test('find by name with default', function () {
        $model = new OverloadedModel();
        $defaultValue = new OverloadedModelItem('Default', 0, 0.0);
        $result = $model->find('Unknown', $defaultValue);
        expect($result)->toBe($defaultValue);
    });

    test('find by key', function () {
        $model = new OverloadedModel();
        $result = $model->find('name', 'Blue');
        $expected = $model->findByKey('name', 'Blue');
        expect($result)->toBe($expected);
    });

    test('find by key with default', function () {
        $model = new OverloadedModel();
        $defaultValue = new OverloadedModelItem('Default', 0, 0.0);
        $result = $model->find('name', 'Unknown', $defaultValue);
        expect($result)->toBe($defaultValue);
    });

    test('error on no matches', function () {
        $model = new OverloadedModel();
        $closure = fn () => $model->find(1, 2, 3, 4);
        expect($closure)->toThrow(BadMethodCallException::class);
    });

    test('error on multiple matches', function () {
        $model = new OverloadedModel();
        $closure = fn () => $model->find('name', 10, 1.2, null);
        expect($closure)->toThrow(BadMethodCallException::class);
    });
});

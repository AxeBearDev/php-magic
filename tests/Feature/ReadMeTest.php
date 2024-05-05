<?php

// Test the examples included in the readme file.

use AxeBear\Magic\Traits\MagicDocBlock;

/**
 * This example class shows how class comments
 * can be used to define magic properties and methods.
 *
 * @property string $name
 *
 * @method self name(string $name)
 * @method string name()
 *
 * @property int $count
 *
 * @method self count(int $count)
 * @method int count()
 *
 * @property string $repeatedName
 * @property-read string $readOnlyValue
 * @property-write string $writeOnlyValue
 */
class ReadMeTestModel
{
    use MagicDocBlock;

    protected string $readOnlyValue = 'Hello, World!';

    public function repeatedName(string $name, int $count): string
    {
        return str_repeat($name, $count);
    }
}

test('ReadMeTestModel', function () {
    $model = new ReadMeTestModel();
    $model->name('Axe Bear');
    $model->count(1);
    $model->writeOnlyValue = 'Hello, World!';

    expect($model->name())->toBe('Axe Bear');
    expect($model->count())->toBe(1);
    expect($model->repeatedName('Axe', 3))->toBe('AxeAxeAxe');
    expect($model->repeatedName)->toBe('Axe Bear');
    expect($model->readOnlyValue)->toBe('Hello, World!');
});

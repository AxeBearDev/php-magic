<?php

use AxeBear\Magic\Attributes\MagicProperty;
use AxeBear\Magic\Traits\MagicProperties;
use AxeBear\Magic\Traits\TrackChanges;

/**
 * @property string $name
 * @property int $age
 * @property float $value
 */
class TrackChangesTestModel
{
    use MagicProperties, TrackChanges;

    #[MagicProperty(onSet: ['strtoupper'])]
    protected string $upperCaseName;
}

test('getChanges', function () {
    $model = new TrackChangesTestModel();
    $model->name = 'Blue';
    $model->age = 25;
    $model->value = 1.2;

    expect($model->getChanges('name'))->toHaveCount(1);
    expect($model->getChanges('age'))->toHaveCount(1);
    expect($model->getChanges('value'))->toHaveCount(1);

    $model->name = 'Red';
    $model->age = 30;
    $model->value = 1.5;

    expect($model->getChanges('name'))->toHaveCount(2);
    expect($model->getChanges('age'))->toHaveCount(2);
    expect($model->getChanges('value'))->toHaveCount(2);
});

test('getAllChanges', function () {
    $model = new TrackChangesTestModel();
    $model->name = 'Blue';
    $model->age = 25;
    $model->value = 1.2;

    expect($model->getAllChanges())->toHaveCount(3);
    expect($model->getAllChanges()['name'])->toHaveCount(1);
    expect($model->getAllChanges()['age'])->toHaveCount(1);
    expect($model->getAllChanges()['value'])->toHaveCount(1);

    $model->name = 'Red';
    $model->age = 30;
    $model->value = 1.5;

    expect($model->getAllChanges())->toHaveCount(3);
    expect($model->getAllChanges()['name'])->toHaveCount(2);
    expect($model->getAllChanges()['age'])->toHaveCount(2);
    expect($model->getAllChanges()['value'])->toHaveCount(2);
});

test('getOriginalValue', function () {
    $model = new TrackChangesTestModel();
    $model->name = 'Blue';
    $model->age = 25;
    $model->value = 1.2;

    $model->name = 'Red';
    $model->age = 30;
    $model->value = 1.5;

    expect($model->getOriginalValue('name'))->toBe('Blue');
    expect($model->getOriginalValue('age'))->toBe(25);
    expect($model->getOriginalValue('value'))->toBe(1.2);
});

test('hasValueChanged', function () {
    $model = new TrackChangesTestModel();
    $model->name = 'Blue';
    $model->age = 25;
    $model->value = 1.2;

    expect($model->hasValueChanged('name'))->toBeFalse();
    expect($model->hasValueChanged('age'))->toBeFalse();
    expect($model->hasValueChanged('value'))->toBeFalse();

    $model->name = 'Red';
    $model->age = 30;
    $model->value = 1.5;

    expect($model->hasValueChanged('name'))->toBeTrue();
    expect($model->hasValueChanged('age'))->toBeTrue();
    expect($model->hasValueChanged('value'))->toBeTrue();
});

test('hasAnyValueChanged', function () {
    $model = new TrackChangesTestModel();
    $model->name = 'Blue';
    $model->age = 25;
    $model->value = 1.2;

    expect($model->hasAnyValueChanged())->toBeFalse();

    $model->name = 'Red';
    $model->age = 30;
    $model->value = 1.5;

    expect($model->hasAnyValueChanged())->toBeTrue();
});

test('resetTrackedChanges', function () {
    $model = new TrackChangesTestModel();
    $model->name = 'Blue';
    $model->age = 25;
    $model->value = 1.2;

    $model->resetTrackedChanges();

    expect($model->getAllChanges())->toBe([]);
    expect($model->hasAnyValueChanged())->toBeFalse();
});

test('track changes after transformations', function () {
    $model = new TrackChangesTestModel();
    $model->upperCaseName = 'Blue';
    $model->upperCaseName = 'Red';

    expect($model->getChanges('upperCaseName'))->toHaveCount(2);
    expect($model->getOriginalValue('upperCaseName'))->toBe('BLUE');
});

<?php

use AxeBear\Magic\Support\Types\ClassCaster as tested;

class ClassCasterTestModel
{
}

test('stdClass', function () {
    expect(tested::supports('stdClass'))->toBeTrue();

    $value = new stdClass();
    expect(tested::cast('stdClass', $value))->toBe($value);
});

test('ClassCaster', function () {
    expect(tested::supports(ClassCasterTestModel::class))->toBeTrue();

    $value = new ClassCasterTestModel();
    expect(tested::cast(ClassCasterTestModel::class, $value))->toBe($value);
});

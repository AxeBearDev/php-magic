<?php

use AxeBear\Magic\Support\Types\ClassConverter as tested;

class ClassConverterTestModel
{
}

test('stdClass', function () {
    expect(tested::supports('stdClass'))->toBeTrue();

    $value = new stdClass();
    expect(tested::convert('stdClass', $value))->toBe($value);
});

test('ClassConverter', function () {
    expect(tested::supports(ClassConverterTestModel::class))->toBeTrue();

    $value = new ClassConverterTestModel();
    expect(tested::convert(ClassConverterTestModel::class, $value))->toBe($value);
});

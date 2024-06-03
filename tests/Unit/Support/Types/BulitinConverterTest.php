<?php

use AxeBear\Magic\Support\Types\BuiltinConverter as tested;

test('integer', function () {
    foreach (['int', 'integer'] as $type) {
        expect(tested::supports($type))->toBeTrue();

        expect(tested::convert($type, '1'))->toBe(1);
        expect(tested::convert($type, 1))->toBe(1);
        expect(tested::convert($type, '1.1'))->toBe(1);
        expect(tested::convert($type, 'a'))->toBe(0);
        expect(tested::convert($type, true))->toBe(1);
        expect(tested::convert($type, false))->toBe(0);
    }
});

test('float', function () {
    foreach (['float', 'double'] as $type) {
        expect(tested::supports($type))->toBeTrue();

        expect(tested::convert($type, '1.1'))->toBe(1.1);
        expect(tested::convert($type, 1.1))->toBe(1.1);
        expect(tested::convert($type, '1'))->toBe(1.0);
        expect(tested::convert($type, 'a'))->toBe(0.0);
    }
});

test('string', function () {
    expect(tested::supports('string'))->toBeTrue();

    expect(tested::convert('string', '1'))->toBe('1');
    expect(tested::convert('string', 1))->toBe('1');
    expect(tested::convert('string', 1.1))->toBe('1.1');
    expect(tested::convert('string', true))->toBe('1');
    expect(tested::convert('string', false))->toBe('');
    expect(tested::convert('string', null))->toBe('');
});

test('bool', function () {
    foreach (['bool', 'boolean'] as $type) {
        expect(tested::supports($type))->toBeTrue();

        expect(tested::convert($type, '1'))->toBeTrue();
        expect(tested::convert($type, 1))->toBeTrue();
        expect(tested::convert($type, '0'))->toBeFalse();
        expect(tested::convert($type, 0))->toBeFalse();
        expect(tested::convert($type, 'a'))->toBeTrue();
        expect(tested::convert($type, true))->toBeTrue();
        expect(tested::convert($type, false))->toBeFalse();
    }
});

test('array', function () {
    expect(tested::supports('array'))->toBeTrue();

    expect(tested::convert('array', '1'))->toBe(['1']);
    expect(tested::convert('array', 1))->toBe([1]);
    expect(tested::convert('array', 1.1))->toBe([1.1]);
    expect(tested::convert('array', true))->toBe([true]);
    expect(tested::convert('array', false))->toBe([false]);
    expect(tested::convert('array', null))->toBe([]);
    expect(tested::convert('array', ['1']))->toBe(['1']);
});

test('object', function () {
    expect(tested::supports('object'))->toBeTrue();

    expect(tested::convert('object', '1'))->toBeInstanceOf(stdClass::class);
    expect(tested::convert('object', 1))->toBeInstanceOf(stdClass::class);
    expect(tested::convert('object', 1.1))->toBeInstanceOf(stdClass::class);
    expect(tested::convert('object', true))->toBeInstanceOf(stdClass::class);
    expect(tested::convert('object', false))->toBeInstanceOf(stdClass::class);
    expect(tested::convert('object', null))->toBeInstanceOf(stdClass::class);
    expect(tested::convert('object', ['1']))->toBeInstanceOf(stdClass::class);
});

test('null', function () {
    expect(tested::supports('null'))->toBeTrue();

    expect(tested::convert('null', '1'))->toBeNull();
    expect(tested::convert('null', 1))->toBeNull();
    expect(tested::convert('null', 1.1))->toBeNull();
    expect(tested::convert('null', true))->toBeNull();
    expect(tested::convert('null', false))->toBeNull();
    expect(tested::convert('null', null))->toBeNull();
    expect(tested::convert('null', ['1']))->toBeNull();
});

<?php

use AxeBear\Magic\Support\Types\BuiltinCaster as tested;

test('integer', function () {
    foreach (['int', 'integer'] as $type) {
        expect(tested::supports($type))->toBeTrue();

        expect(tested::cast($type, '1'))->toBe(1);
        expect(tested::cast($type, 1))->toBe(1);
        expect(tested::cast($type, '1.1'))->toBe(1);
        expect(tested::cast($type, 'a'))->toBe(0);
        expect(tested::cast($type, true))->toBe(1);
        expect(tested::cast($type, false))->toBe(0);
    }
});

test('float', function () {
    foreach (['float', 'double'] as $type) {
        expect(tested::supports($type))->toBeTrue();

        expect(tested::cast($type, '1.1'))->toBe(1.1);
        expect(tested::cast($type, 1.1))->toBe(1.1);
        expect(tested::cast($type, '1'))->toBe(1.0);
        expect(tested::cast($type, 'a'))->toBe(0.0);
    }
});

test('string', function () {
    expect(tested::supports('string'))->toBeTrue();

    expect(tested::cast('string', '1'))->toBe('1');
    expect(tested::cast('string', 1))->toBe('1');
    expect(tested::cast('string', 1.1))->toBe('1.1');
    expect(tested::cast('string', true))->toBe('1');
    expect(tested::cast('string', false))->toBe('');
    expect(tested::cast('string', null))->toBe('');
});

test('bool', function () {
    foreach (['bool', 'boolean'] as $type) {
        expect(tested::supports($type))->toBeTrue();

        expect(tested::cast($type, '1'))->toBeTrue();
        expect(tested::cast($type, 1))->toBeTrue();
        expect(tested::cast($type, '0'))->toBeFalse();
        expect(tested::cast($type, 0))->toBeFalse();
        expect(tested::cast($type, 'a'))->toBeTrue();
        expect(tested::cast($type, true))->toBeTrue();
        expect(tested::cast($type, false))->toBeFalse();
    }
});

test('array', function () {
    expect(tested::supports('array'))->toBeTrue();

    expect(tested::cast('array', '1'))->toBe(['1']);
    expect(tested::cast('array', 1))->toBe([1]);
    expect(tested::cast('array', 1.1))->toBe([1.1]);
    expect(tested::cast('array', true))->toBe([true]);
    expect(tested::cast('array', false))->toBe([false]);
    expect(tested::cast('array', null))->toBe([]);
    expect(tested::cast('array', ['1']))->toBe(['1']);
});

test('object', function () {
    expect(tested::supports('object'))->toBeTrue();

    expect(tested::cast('object', '1'))->toBeInstanceOf(stdClass::class);
    expect(tested::cast('object', 1))->toBeInstanceOf(stdClass::class);
    expect(tested::cast('object', 1.1))->toBeInstanceOf(stdClass::class);
    expect(tested::cast('object', true))->toBeInstanceOf(stdClass::class);
    expect(tested::cast('object', false))->toBeInstanceOf(stdClass::class);
    expect(tested::cast('object', null))->toBeInstanceOf(stdClass::class);
    expect(tested::cast('object', ['1']))->toBeInstanceOf(stdClass::class);
});

test('null', function () {
    expect(tested::supports('null'))->toBeTrue();

    expect(tested::cast('null', '1'))->toBeNull();
    expect(tested::cast('null', 1))->toBeNull();
    expect(tested::cast('null', 1.1))->toBeNull();
    expect(tested::cast('null', true))->toBeNull();
    expect(tested::cast('null', false))->toBeNull();
    expect(tested::cast('null', null))->toBeNull();
    expect(tested::cast('null', ['1']))->toBeNull();
});

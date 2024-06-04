<?php

use AxeBear\Magic\Support\Types\IntRangeCaster as tested;

describe('specific range', function () {
    test('int<min, max>', function () {
        expect(tested::supports('int<min, max>'))->toBeTrue();
        expect(tested::cast('int<min, max>', PHP_INT_MIN))->toBe(PHP_INT_MIN);
        expect(tested::cast('int<min, max>', PHP_INT_MAX))->toBe(PHP_INT_MAX);
    });

    test('int<0, 100>', function () {
        expect(tested::supports('int<0, 100>'))->toBeTrue();
        expect(tested::cast('int<0, 100>', 0))->toBe(0);
        expect(tested::cast('int<0, 100>', 100))->toBe(100);
        expect(fn () => tested::cast('int<0, 100>', -1))->toThrow(\OutOfRangeException::class);
    });

    test('int<50, max>', function () {
        expect(tested::supports('int<50, max>'))->toBeTrue();
        expect(tested::cast('int<50, max>', 50))->toBe(50);
        expect(tested::cast('int<50, max>', PHP_INT_MAX))->toBe(PHP_INT_MAX);
        expect(fn () => tested::cast('int<50, max>', 49))->toThrow(\OutOfRangeException::class);
    });

    test('int<min, 100>', function () {
        expect(tested::supports('int<min, 100>'))->toBeTrue();
        expect(tested::cast('int<min, 100>', PHP_INT_MIN))->toBe(PHP_INT_MIN);
        expect(tested::cast('int<min, 100>', 100))->toBe(100);
        expect(fn () => tested::cast('int<min, 100>', 101))->toThrow(\OutOfRangeException::class);
    });
});

test('non-zero-int', function () {
    expect(tested::supports('non-zero-int'))->toBeTrue();
    expect(tested::cast('non-zero-int', 1))->toBe(1);
    expect(tested::cast('non-zero-int', -1))->toBe(-1);
    expect(fn () => tested::cast('non-zero-int', 0))->toThrow(\OutOfRangeException::class);
});

test('positive-int', function () {
    expect(tested::supports('positive-int'))->toBeTrue();
    expect(tested::cast('positive-int', 1))->toBe(1);
    expect(fn () => tested::cast('positive-int', 0))->toThrow(\OutOfRangeException::class);
    expect(fn () => tested::cast('positive-int', -1))->toThrow(\OutOfRangeException::class);
});

test('non-negative-int', function () {
    expect(tested::supports('non-negative-int'))->toBeTrue();
    expect(tested::cast('non-negative-int', 1))->toBe(1);
    expect(tested::cast('non-negative-int', 0))->toBe(0);
    expect(fn () => tested::cast('non-negative-int', -1))->toThrow(\OutOfRangeException::class);
});

test('negative-int', function () {
    expect(tested::supports('negative-int'))->toBeTrue();
    expect(tested::cast('negative-int', -1))->toBe(-1);
    expect(fn () => tested::cast('negative-int', 0))->toThrow(\OutOfRangeException::class);
    expect(fn () => tested::cast('negative-int', 1))->toThrow(\OutOfRangeException::class);
});

test('non-positive-int', function () {
    expect(tested::supports('non-positive-int'))->toBeTrue();
    expect(tested::cast('non-positive-int', -1))->toBe(-1);
    expect(tested::cast('non-positive-int', 0))->toBe(0);
    expect(fn () => tested::cast('non-positive-int', 1))->toThrow(\OutOfRangeException::class);
});

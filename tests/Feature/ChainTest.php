<?php

use AxeBear\Magic\Support\Chain;

describe('Chain', function () {
    test('push', function () {
        $chain = new Chain;
        $chain->push(fn ($x) => $x + 1);
        $chain->push(fn ($x) => $x * 2);
        $chain->push(fn ($x) => $x - 1);
        $result = $chain(1);
        expect($result)->toBe(3);
    });

    test('together', function () {
        $chain = Chain::together(
            fn ($x) => $x + 1,
            fn ($x) => $x * 2,
            fn ($x) => $x - 1,
        );
        $result = $chain(1);
        expect($result)->toBe(3);
    });

    test('onError', function () {
        $chain = Chain::together(
            fn ($x) => $x + 1,
            fn ($x) => throw new Exception('error'),
            fn ($x) => $x - 1,
        )->onError(fn ($e, $carry, $link) => $carry);
        $result = $chain(1);
        expect($result)->toBe(1);
    });

    test('until', function () {
        $chain = Chain::together(
            fn ($x) => $x + 1,
            fn ($x) => $x * 2,
            fn ($x) => $x - 1,
        )->until(fn ($x) => $x > 5);
        expect($chain(1))->toBe(3);
        expect($chain(2))->toBe(6);
    });

    test('then', function () {
        $chain = Chain::together(
            fn ($x) => $x + 1,
            fn ($x) => $x * 2,
            fn ($x) => $x - 1,
        )->until(fn ($x) => $x > 5)
          ->then(fn ($x) => $x * 2);
        expect($chain(1))->toBe(6);
        expect($chain(2))->toBe(12);
    });

    test('each', function () {
        $chain = Chain::together(
            fn ($x) => $x + 1,
            fn ($x) => $x * 2,
            fn ($x) => $x - 1,
        )->each(fn ($link) => fn ($x) => $link($x + 1));
        expect($chain(1))->toBe(8);
        expect($chain(2))->toBe(10);
    });

    test('carryInput', function () {
        $test = new stdClass;
        $test->value = 1;

        $chain = Chain::together(
            fn ($x) => $x->value += 1,
            fn ($x) => $x->value *= 2,
            fn ($x) => $x->value -= 1,
        )->carryInput();
        expect($chain($test))->toBeInstanceOf(stdClass::class);
        expect($test->value)->toBe(3);
    });
});

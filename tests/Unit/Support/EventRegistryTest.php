<?php

use AxeBear\Magic\Support\EventRegistry;

test('invokable', function () {
    $e = new EventRegistry();
    $e('event', fn () => 'handler');
    expect($e->find('event'))->toHaveCount(1);
});

test('on', function () {
    $e = new EventRegistry();
    $e->on('event', fn () => 'handler');
    expect($e->find('event'))->toHaveCount(1);
});

test('unset', function () {
    $e = new EventRegistry();
    $e->on('event', fn () => 'handler');
    $e->unset('event');
    expect($e->find('event'))->toHaveCount(0);
});

test('has', function () {
    $e = new EventRegistry();
    $e->on('event', fn () => 'handler');
    expect($e->has('event'))->toBeTrue();
});

describe('handles', function () {
    test('simple match', function () {
        $e = new EventRegistry();
        $e->on('event', fn () => 'handler');
        expect($e->handles('event'))->toBeTrue();
    });

    test('fnmatch', function () {
        $e = new EventRegistry();
        $e->on('event.*', fn () => 'handler');
        expect($e->handles('event.1'))->toBeTrue();
    });

    test('no match', function () {
        $e = new EventRegistry();
        $e->on('event', fn () => 'handler');
        expect($e->handles('nope'))->toBeFalse();
    });
});

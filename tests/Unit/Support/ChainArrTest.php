<?php

use AxeBear\Magic\Support\Arr;
use AxeBear\Magic\Support\Chain;

test('map', function () {
    $chain = Chain::together(
        Arr::map(fn ($x) => $x + 1)
    );
    expect($chain([1, 2, 3]))->toBe([2, 3, 4]);
});

describe('where', function () {
    test('without keys', function () {
        $chain = Chain::together(
            Arr::where(
                fn ($x) => $x % 2 === 0
            ),
            Arr::values()
        );
        expect($chain([1, 2, 3, 4, 5]))->toBe([2, 4]);
    });

    test('with keys', function () {
        $chain = Chain::together(
            Arr::where(
                fn ($value, $key) => $value > 1 || $key === 'a'
            ),
        );
        expect($chain(['a' => 1, 'b' => 2, 'c' => 0]))
          ->toBe(['a' => 1, 'b' => 2]);
    });

    test('whereTruthy', function () {
        $chain = Chain::together(
            Arr::whereTruthy(),
            Arr::values()
        );
        expect($chain([0, 1, 2, 3, 4]))->toBe([1, 2, 3, 4]);
    });

    test('whereTruthy with keys', function () {
        $chain = Chain::together(
            Arr::whereTruthy(),
        );
        expect($chain(['a' => 0, 'b' => 1, 'c' => 2]))
          ->toBe(['b' => 1, 'c' => 2]);
    });

    test('whereFalsy', function () {
        $chain = Chain::together(
            Arr::whereFalsy(),
            Arr::values()
        );
        expect($chain([0, 1, 2, 3, 4]))->toBe([0]);
    });

    test('whereFalsy with keys', function () {
        $chain = Chain::together(
            Arr::whereFalsy(),
        );
        expect($chain(['a' => 0, 'b' => 1, 'c' => 2]))
          ->toBe(['a' => 0]);
    });
});

describe('sort', function () {
    test('ascending', function () {
        $chain = Chain::together(
            Arr::sort()
        );
        expect($chain([3, 1, 2]))->toBe([1, 2, 3]);
    });

    test('descending', function () {
        $reverse = Chain::together(
            Arr::sort(false)
        );
        expect($reverse([3, 1, 2]))->toBe([3, 2, 1]);
    });

    test('custom', function () {
        $custom = Chain::together(
            Arr::sort(fn ($a, $b) => $b <=> $a)
        );
        expect($custom([3, 1, 2]))->toBe([3, 2, 1]);
    });
});

describe('first', function () {
    test('value', function () {
        $chain = Chain::together(Arr::first());
        expect($chain(['a' => 1, 'b' => 2]))->toBe(1);
    });

    test('with function', function () {
        $chain = Chain::together(
            Arr::first(fn ($x) => $x % 2 === 0)
        );
        expect($chain([1, 2, 3, 4, 5]))->toBe(2);
    });

    test('with default', function () {
        $chain = Chain::together(
            Arr::first(null, 0)
        );
        expect($chain([]))->toBe(0);
    });

    test('with keys', function () {
        $chain = Chain::together(
            Arr::first(null, null, true)
        );
        expect($chain(['a' => 1, 'b' => 2]))->toBe(['a' => 1]);
    });
});

describe('boolean tests', function () {
    test('any', function () {
        $chain = Chain::together(
            Arr::any(fn ($x) => $x % 2 === 0)
        );
        expect($chain([1, 2, 3, 4, 5]))->toBeTrue();
        expect($chain([1, 3, 5]))->toBeFalse();
    });

    test('all', function () {
        $chain = Chain::together(
            Arr::all(fn ($x) => $x % 2 === 0)
        );
        expect($chain([1, 2, 3, 4, 5]))->toBeFalse();
        expect($chain([2, 4]))->toBeTrue();
    });

    test('none', function () {
        $chain = Chain::together(
            Arr::none(fn ($x) => $x % 2 === 0)
        );
        expect($chain([1, 2, 3, 4, 5]))->toBeFalse();
        expect($chain([1, 3, 5]))->toBeTrue();
    });
});

describe('unique', function () {
    test('without keys', function () {
        $chain = Chain::together(
            Arr::unique(),
            Arr::values()
        );
        expect($chain([1, 2, 1, 3, 2]))->toBe([1, 2, 3]);
    });

    test('with keys', function () {
        $chain = Chain::together(
            Arr::unique()
        );
        expect($chain([1, 2, 1, 3, 2]))->toBe([0 => 1, 1 => 2, 3 => 3]);
    });

    test('with getValue', function () {
        $chain = Chain::together(
            Arr::unique(fn ($value) => $value['a'])
        );
        expect($chain([
            ['a' => 1],
            ['a' => 2],
            ['a' => 1],
        ]))
          ->toBe([
              ['a' => 1],
              ['a' => 2],
          ]);
    });
});

describe('groupBy', function () {
    test('with key', function () {
        $chain = Chain::together(
            Arr::groupBy(fn ($x) => $x % 2)
        );
        expect($chain([1, 2, 3, 4, 5]))->toBe([
            1 => [1, 3, 5],
            0 => [2, 4],
        ]);
    });

    test('with index', function () {
        $chain = Chain::together(
            Arr::groupBy(fn ($x, $i) => $i % 2)
        );
        expect($chain([0, 1, 2, 3, 4, 5]))->toBe([
            0 => [0, 2, 4],
            1 => [1, 3, 5],
        ]);
    });
});

describe('flatten', function () {
    test('simple', function () {
        $chain = Chain::together(
            Arr::flatten()
        );
        expect($chain([[1, 2], [3, 4], [5, 6]]))->toBe([1, 2, 3, 4, 5, 6]);
    });

    test('deep', function () {
        $chain = Chain::together(
            Arr::flatten()
        );
        expect($chain([1, [2, [3, [4, [5]]]]]))->toBe([1, 2, 3, 4, 5]);
    });

    test('with keys', function () {
        $chain = Chain::together(
            Arr::flatten()
        );
        expect($chain(['a' => [1, 2], 'b' => [3, 4]]))
          ->toBe([1, 2, 3, 4]);
    });
});

describe('each', function () {
    test('simple', function () {
        $count = new stdClass;
        $count->value = 0;

        $chain = Chain::together(
            Arr::each(fn ($item) => $count->value += $item)
        );
        expect($chain([1, 2, 3]))->toBe([1, 2, 3]);
        expect($count->value)->toBe(6);
    });

    test('simple with return', function () {
        $count = new stdClass;
        $count->value = 0;
        $chain = Chain::together(
            Arr::each(fn ($item) => $count->value += $item),
            fn ($array) => [$array, $count->value]
        );
        expect($chain([1, 2, 3]))->toBe([[1, 2, 3], 6]);
    });
});

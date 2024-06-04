<?php

use AxeBear\Magic\Support\Types\TypedArrayCaster as tested;

$expectations = [
    'int' => [
        ['1', 2, '3.3', true, false],
        [1, 2, 3, 1, 0],
    ],
    'float' => [
        ['1', 2, '3.3', true, false],
        [1.0, 2.0, 3.3, 1.0, 0.0],
    ],
    'string' => [
        [1, 2, 3.3, true, false, null],
        ['1', '2', '3.3', '1', '', ''],
    ],
    'bool' => [
        ['1', 2, '3.3', true, false],
        [true, true, true, true, false],
    ],
];

test('non-empty-array', function () {
    $input = [1, 2, 3];
    $output = [1, 2, 3];
    $type = 'non-empty-array<int>';
    expect(tested::supports($type))->toBeTrue();
    $converted = tested::cast($type, $input);
    expect($converted)->toBe($output);

    $input = [];
    $type = 'non-empty-array<int>';
    expect(tested::supports($type))->toBeTrue();
    expect(fn () => tested::cast($type, $input))->toThrow(\OutOfRangeException::class);
});

describe('nested arrays', function () {
    test('array<array<string>>', function () {
        $input = [['1', 2], [3.3, true], [false, null]];
        $output = [['1', '2'], ['3.3', '1'], ['', '']];
        $type = 'array<array<string>>';
        expect(tested::supports($type))->toBeTrue();
        $converted = tested::cast($type, $input);
        expect($converted)->toBe($output);
    });

    test('array<string, array<string>>', function () {
        $input = ['a' => ['1', 2], 'b' => [3.3, true], 'c' => [false, null]];
        $output = ['a' => ['1', '2'], 'b' => ['3.3', '1'], 'c' => ['', '']];
        $type = 'array<string, array<string>>';
        expect(tested::supports($type))->toBeTrue();
        $converted = tested::cast($type, $input);
        expect($converted)->toBe($output);
    });
});

foreach ($expectations as $type => $data) {
    [$input, $output] = $data;

    $variants = [
        $type.'[]',
        'array<'.$type.'>',
        'array<int, '.$type.'>',
        'non-empty-array<'.$type.'>',
        'non-empty-array<int, '.$type.'>',
        'iterable<'.$type.'>',
        'Collection<'.$type.'>',
        'Collection<int, '.$type.'>',
    ];

    foreach ($variants as $variant) {
        test($variant, function () use ($variant, $input, $output) {
            expect(tested::supports($variant))->toBeTrue();
            $converted = tested::cast($variant, $input);
            expect($converted)->toBe($output);
        });
    }
}

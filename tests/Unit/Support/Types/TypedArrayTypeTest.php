<?php

use AxeBear\Magic\Support\Types\TypedArrayType;

test('Invalid type', function () {
    $type = new TypedArrayType('invalid');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBeFalse();
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeFalse();
});

describe('Type[]', function () {
    test('int[]', function () {
        $type = new TypedArrayType('int[]');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('int');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('float[]', function () {
        $type = new TypedArrayType('float[]');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('float');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('Lorem[]', function () {
        $type = new TypedArrayType('Lorem[]');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('Lorem');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });
});

describe('array<Type>', function () {
    test('array<int>', function () {
        $type = new TypedArrayType('array<int>');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('int');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('array<float>', function () {
        $type = new TypedArrayType('array<float>');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('float');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('array<Lorem>', function () {
        $type = new TypedArrayType('array<Lorem>');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('Lorem');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });
});

describe('array<key, Type>', function () {
    test('array<int, int>', function () {
        $type = new TypedArrayType('array<int, int>');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('int');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('array<string, float>', function () {
        $type = new TypedArrayType('array<string, float>');
        expect($type->keyType)->toBe('string');
        expect($type->valueType)->toBe('float');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('array<bool, Lorem>', function () {
        $type = new TypedArrayType('array<bool, Lorem>');
        expect($type->keyType)->toBe('bool');
        expect($type->valueType)->toBe('Lorem');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });
});

test('non-empty-array', function () {
    $type = new TypedArrayType('non-empty-array<int>');
    expect($type->nonEmpty)->toBeTrue();

    $type = new TypedArrayType('non-empty-array<float, float>');
    expect($type->nonEmpty)->toBeTrue();
});

describe('Collection', function () {
    test('Collection<string>', function () {
        $type = new TypedArrayType('Collection<string>');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('string');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('Collection<string, Lorem>', function () {
        $type = new TypedArrayType('Collection<string, Lorem>');
        expect($type->keyType)->toBe('string');
        expect($type->valueType)->toBe('Lorem');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });
});

describe('iterable', function () {
    test('iterable<int>', function () {
        $type = new TypedArrayType('iterable<int>');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('int');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('iterable<string, float>', function () {
        $type = new TypedArrayType('iterable<string, float>');
        expect($type->keyType)->toBe('string');
        expect($type->valueType)->toBe('float');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });
});

describe('nested arrays', function () {
    test('array<array<string>>', function () {
        $type = new TypedArrayType('array<array<string>>');
        expect($type->keyType)->toBe('int');
        expect($type->valueType)->toBe('array<string>');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });

    test('array<string, array<string>>', function () {
        $type = new TypedArrayType('array<string, array<string>>');
        expect($type->keyType)->toBe('string');
        expect($type->valueType)->toBe('array<string>');
        expect($type->nonEmpty)->toBeFalse();
        expect($type->valid)->toBeTrue();
    });
});

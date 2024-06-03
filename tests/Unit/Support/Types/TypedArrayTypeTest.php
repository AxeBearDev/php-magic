<?php

use AxeBear\Magic\Support\Types\TypedArrayType;

test('Invalid type', function () {
    $type = new TypedArrayType('invalid');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBeFalse();
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeFalse();
});

test('Type[]', function () {
    $type = new TypedArrayType('int[]');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('int');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();

    $type = new TypedArrayType('float[]');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('float');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();

    $type = new TypedArrayType('Lorem[]');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('Lorem');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();
});

test('array<Type>', function () {
    $type = new TypedArrayType('array<int>');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('int');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();

    $type = new TypedArrayType('array<float>');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('float');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();

    $type = new TypedArrayType('array<Lorem>');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('Lorem');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();
});

test('array<key, Type>', function () {
    $type = new TypedArrayType('array<int, int>');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('int');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();

    $type = new TypedArrayType('array<string, float>');
    expect($type->keyType)->toBe('string');
    expect($type->valueType)->toBe('float');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();

    $type = new TypedArrayType('array<bool, Lorem>');
    expect($type->keyType)->toBe('bool');
    expect($type->valueType)->toBe('Lorem');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();
});

test('non-empty-array', function () {
    $type = new TypedArrayType('non-empty-array<int>');
    expect($type->nonEmpty)->toBeTrue();

    $type = new TypedArrayType('non-empty-array<float, float>');
    expect($type->nonEmpty)->toBeTrue();
});

test('Collection', function () {
    $type = new TypedArrayType('Collection<string>');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('string');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();

    $type = new TypedArrayType('Collection<string, Lorem>');
    expect($type->keyType)->toBe('string');
    expect($type->valueType)->toBe('Lorem');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();
});

test('iterable', function () {
    $type = new TypedArrayType('iterable<int>');
    expect($type->keyType)->toBe('int');
    expect($type->valueType)->toBe('int');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();

    $type = new TypedArrayType('iterable<string, float>');
    expect($type->keyType)->toBe('string');
    expect($type->valueType)->toBe('float');
    expect($type->nonEmpty)->toBeFalse();
    expect($type->valid)->toBeTrue();
});

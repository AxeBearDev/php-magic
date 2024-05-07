<?php

use AxeBear\Magic\Traits\Reflections;

class ReflectionModel
{
    use Reflections;
}

describe('Reflections Trait', function () {
    test('methodAllowsArguments', function () {
        $model = new ReflectionModel();
        $method = new ReflectionMethod($model, 'methodAllowsArguments');
        $arguments = [1, 2, 3];
        $result = $model->methodAllowsArguments($method, $arguments);
        expect($result)->toBeTrue();
    });

    test('parameterAllowsValue', function () {
        $model = new ReflectionModel();
        $param = new ReflectionParameter(fn ($a, $b) => null, 0);
        $value = 1;
        $result = $model->parameterAllowsValue($param, $value);
        expect($result)->toBeTrue();
    });

    test('parameterAllowsValue with null', function () {
        $model = new ReflectionModel();
        $param = new ReflectionParameter(fn ($a, $b) => null, 0);
        $value = null;
        $result = $model->parameterAllowsValue($param, $value);
        expect($result)->toBeTrue();
    });

    test('parameterAllowsValue with null and optional', function () {
        $model = new ReflectionModel();
        $param = new ReflectionParameter(fn ($a, $b = null) => null, 0);
        $value = null;
        $result = $model->parameterAllowsValue($param, $value);
        expect($result)->toBeTrue();
    });

    test('parameterAllowsValue with null and nullable', function () {
        $model = new ReflectionModel();
        $param = new ReflectionParameter(fn (?int $a) => null, 0);
        $value = null;
        $result = $model->parameterAllowsValue($param, $value);
        expect($result)->toBeTrue();
    });

    test('parameterAllowsValue with null and neither optional nor nullable', function () {
        $model = new ReflectionModel();
        $param = new ReflectionParameter(fn (int $a) => null, 0);
        $value = null;
        $result = $model->parameterAllowsValue($param, $value);
        expect($result)->toBeFalse();
    });
});

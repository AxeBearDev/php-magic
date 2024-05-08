<?php

use AxeBear\Magic\Traits\Reflections;

class ReflectionsTester
{
    use Reflections;

    protected function count(int $number): int
    {
        return $number;
    }
}
class ReflectionsTesterChild extends ReflectionsTester
{
}

describe('typeAllowsValue', function () {
    test('mixed type', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (mixed $a) => null, 0);
        $type = $param->getType();
        expect($tester->typeAllowsValue($type, 1))->toBeTrue();
        expect($tester->typeAllowsValue($type, '1'))->toBeTrue();
        expect($tester->typeAllowsValue($type, null))->toBeTrue();
    });

    test('built-in type', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (int $a) => null, 0);
        $type = $param->getType();
        expect($tester->typeAllowsValue($type, 1))->toBeTrue();
        expect($tester->typeAllowsValue($type, '1'))->toBeFalse();
        expect($tester->typeAllowsValue($type, null))->toBeFalse();
    });

    test('interface', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (Countable $a) => null, 0);
        $type = $param->getType();
        expect($tester->typeAllowsValue($type, new ArrayObject()))->toBeTrue();
        expect($tester->typeAllowsValue($type, 1))->toBeFalse();
        expect($tester->typeAllowsValue($type, null))->toBeFalse();
    });

    test('iterable', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (iterable $a) => null, 0);
        $type = $param->getType();
        expect($tester->typeAllowsValue($type, new ArrayObject()))->toBeTrue();
        expect($tester->typeAllowsValue($type, [1]))->toBeTrue();
        expect($tester->typeAllowsValue($type, 1))->toBeFalse();
        expect($tester->typeAllowsValue($type, null))->toBeFalse();
    });

    test('array', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (array $a) => null, 0);
        $type = $param->getType();
        expect($tester->typeAllowsValue($type, new ArrayObject()))->toBeFalse();
        expect($tester->typeAllowsValue($type, [1]))->toBeTrue();
        expect($tester->typeAllowsValue($type, 1))->toBeFalse();
        expect($tester->typeAllowsValue($type, null))->toBeFalse();
    });

    test('class', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (ReflectionsTester $a) => null, 0);
        $type = $param->getType();
        expect($tester->typeAllowsValue($type, $tester))->toBeTrue();
        expect($tester->typeAllowsValue($type, 1))->toBeFalse();
        expect($tester->typeAllowsValue($type, null))->toBeFalse();
    });

    test('child class', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (ReflectionsTester $a) => null, 0);
        $type = $param->getType();
        expect($tester->typeAllowsValue($type, new ReflectionsTesterChild()))->toBeTrue();
        expect($tester->typeAllowsValue($type, $tester))->toBeTrue();
        expect($tester->typeAllowsValue($type, 1))->toBeFalse();
        expect($tester->typeAllowsValue($type, null))->toBeFalse();
    });
});

describe('methodAllowsArguments', function () {
    test('expected', function () {
        $tester = new ReflectionsTester();
        $method = new ReflectionMethod($tester, 'count');
        $arguments = [1];
        $result = $tester->methodAllowsArguments($method, $arguments);
        expect($result)->toBeTrue();
    });
});

describe('parameterAllowsValue', function () {
    test('no type', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn ($a, $b) => null, 0);
        expect($tester->parameterAllowsValue($param, 1))->toBeTrue();
        expect($tester->parameterAllowsValue($param, '1'))->toBeTrue();
    });

    test('declared type', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (int $a) => null, 0);
        expect($tester->parameterAllowsValue($param, 1))->toBeTrue();
        expect($tester->parameterAllowsValue($param, '1'))->toBeFalse();
    });

    test('mixed type', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (mixed $a) => null, 0);
        expect($tester->parameterAllowsValue($param, 1))->toBeTrue();
        expect($tester->parameterAllowsValue($param, '1'))->toBeTrue();
        expect($tester->parameterAllowsValue($param, null))->toBeTrue();
    });

    test('union type', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (int|string $a) => null, 0);
        expect($tester->parameterAllowsValue($param, 1))->toBeTrue();
        expect($tester->parameterAllowsValue($param, '1'))->toBeTrue();
        expect($tester->parameterAllowsValue($param, null))->toBeFalse();
    });

    test('object type', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (ReflectionsTester $a) => null, 0);
        expect($tester->parameterAllowsValue($param, $tester))->toBeTrue();
        expect($tester->parameterAllowsValue($param, 1))->toBeFalse();
        expect($tester->parameterAllowsValue($param, null))->toBeFalse();
    });

    test('null and optional', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn ($a, $b = null) => null, 1);
        $value = null;
        $result = $tester->parameterAllowsValue($param, $value);
        expect($result)->toBeTrue();
    });

    test('null and nullable', function () {
        $tester = new ReflectionsTester();
        $param = new ReflectionParameter(fn (?int $a) => null, 0);
        $value = null;
        $result = $tester->parameterAllowsValue($param, $value);
        expect($result)->toBeTrue();
    });
});

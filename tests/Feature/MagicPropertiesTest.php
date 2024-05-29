<?php

use AxeBear\Magic\Attributes\MagicProperty;
use AxeBear\Magic\Exceptions\MagicException;
use AxeBear\Magic\Traits\MagicProperties;

/**
 * Sample class used for testing the #[MagicProperty] attribute and @property tag.
 *
 * Bound properties:
 *
 * @property int $count
 * @property string $name
 * @property-read string $repeatedName
 * @property bool $leaving
 * @property string $title
 * @property stdClass $subtitle
 * @property-read string $greeting
 * @property-read string $message
 * @property-write string $farewell
 * @property-read string $uncachedName
 *
 * Unbound properties:
 * @property int $unboundNumber
 * @property string $unboundString
 * @property bool $unboundBool
 * @property array $unboundArray
 * @property object $unboundObject
 * @property float $unboundFloat
 *
 * Fluent methods:
 *
 * @method self count(int $value)
 * @method int count()
 * @method string name()
 * @method self title(string $value)
 * @method self unboundString(string $value)
 * @method string unboundString()
 */
class MagicPropertiesTestModel
{
    use MagicProperties;

    protected int $count = 0;

    protected bool $leaving = false;

    protected string $name = 'Axe';

    protected function repeatedName(int $count, string $name): string
    {
        return str_repeat($name, $count);
    }

    #[MagicProperty(aliases: ['greeting', 'howdy', 'hello'])]
    protected string $greeting = 'Hello, World!';

    protected string $farewell = 'Goodbye, World!';

    #[MagicProperty(onSet: ['customUpperCase'])]
    protected string $title = '';

    #[MagicProperty(onGet: ['strtoupper'])]
    protected string $slug = '';

    #[MagicProperty(onSet: ['json_encode'], onGet: ['json_decode'])]
    protected string $subtitle = '';

    protected function message(bool $leaving): string
    {
        return $leaving ? $this->farewell : $this->greeting;
    }

    #[MagicProperty(disableCache: true)]
    protected function uncachedName(): string
    {
        return $this->name;
    }

    protected function customUpperCase(string $value): string
    {
        return strtoupper($value);
    }
}

describe('DocBlock settings', function () {
    test('basic getters and setters', function () {
        $model = new MagicPropertiesTestModel();
        expect($model->name)->toBe('Axe');
        expect($model->leaving)->toBe(false);

        $model->name = 'Bear';
        $model->leaving = true;

        expect($model->name)->toBe('Bear');
        expect($model->leaving)->toBe(true);
    });

    test('calculated methods', function () {
        $model = new MagicPropertiesTestModel();

        $model->leaving = true;
        expect($model->message)->toBe('Goodbye, World!');

        $model->leaving = false;
        expect($model->message)->toBe('Hello, World!');

        $model->count = 3;
        $model->name = 'X';
        expect($model->repeatedName)->toBe('XXX');
    });
});

describe('MagicProperty Attribute', function () {
    test('access', function () {
        $model = new MagicPropertiesTestModel();

        $this->expectException(MagicException::class);
        $model->farewell;

        $this->expectException(MagicException::class);

        /**
         * @php-ignore
         *
         * @disregard
         */
        $model->greeting = 'Hello, Axe!';
    });

    test('onGet', function () {
        $model = new MagicPropertiesTestModel();
        $model->slug = 'oozy the doozy';
        expect($model->slug)->toBe('OOZY THE DOOZY');
        expect($model->getRawValue('slug'))->toBe('oozy the doozy');
    });

    test('onSet', function () {
        $model = new MagicPropertiesTestModel();
        $model->title = 'axebear';
        expect($model->title)->toBe('AXEBEAR');
    });

    test('onGet and onSet', function () {
        $model = new MagicPropertiesTestModel();
        $model->subtitle = ['key' => 'value'];
        expect($model->subtitle)->toBeInstanceOf(stdClass::class);
        expect($model->subtitle->key)->toBe('value');
        $rawSubtitle = $model->getRawValue('subtitle');
        expect($rawSubtitle)->toBe('{"key":"value"}');
    });

    test('uncached properties', function () {
        $model = new MagicPropertiesTestModel();
        $model->name = 'Axe';
        expect($model->uncachedName)->toBe('Axe');
        $model->name = 'Bear';
        expect($model->uncachedName)->toBe('Bear');
    });

    test('aliases', function () {
        $model = new MagicPropertiesTestModel();
        expect($model->greeting)->toBe($model->hello);
        expect($model->greeting)->toBe($model->howdy);
    });
});

describe('Unbound Properties', function () {
    test('get, set and getRawValue', function () {
        $model = new MagicPropertiesTestModel();
        expect($model->unboundNumber)->toBeNull();
        $model->unboundNumber = '1';
        expect($model->unboundNumber)->toBe(1);
        expect($model->getRawValue('unboundNumber'))->toBe(1);
    });

    test('type conversion', function () {
        $model = new MagicPropertiesTestModel();

        $model->unboundNumber = '1';
        expect($model->unboundNumber)->toBe(1);

        $model->unboundString = 1;
        expect($model->unboundString)->toBe('1');

        $model->unboundBool = 'true';
        expect($model->unboundBool)->toBe(true);
        $model->unboundBool = '0';
        expect($model->unboundBool)->toBe(false);

        $model->unboundArray = '1';
        expect($model->unboundArray)->toBe(['1']);

        $model->unboundObject = '["key":"value"]';
        expect($model->unboundObject)->toBeInstanceOf(stdClass::class);

        $model->unboundFloat = '1.1';
        expect($model->unboundFloat)->toBe(1.1);

        $model->unboundFloat = '1';
        expect($model->unboundFloat)->toBe(1.0);
    });
});

describe('Fluent Methods', function () {
    test('getters', function () {
        $model = new MagicPropertiesTestModel();
        expect($model->count())->toBe(0);
        expect($model->name())->toBe('Axe');
    });

    test('setters', function () {
        $model = new MagicPropertiesTestModel();
        $model->count(1);
        $model->title('AxeBear');

        expect($model->count)->toBe(1);
        expect($model->title)->toBe('AXEBEAR');
    });

    test('return self', function () {
        $model = new MagicPropertiesTestModel();
        expect($model->unboundString())->toBeNull();
        expect($model->unboundString('AxeBear'))->toBeInstanceOf(MagicPropertiesTestModel::class);
        expect($model->unboundString())->toBe('AxeBear');
    });

    test('invalid argument lengths', function () {
        $model = new MagicPropertiesTestModel();

        $this->expectException(InvalidArgumentException::class);
        $model->name(1);

        $this->expectException(InvalidArgumentException::class);
        $model->name(1, 2);

        $this->expectException(InvalidArgumentException::class);
        $model->title;
    });
});

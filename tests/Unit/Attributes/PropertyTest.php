<?php

use AxeBear\Magic\Attributes\Property;
use AxeBear\Magic\Exceptions\MagicException;
use AxeBear\Magic\Traits\Properties;

/**
 * Sample class used for testing the #[Property] attribute and @property tag.
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
 * // Unbound properties
 * @property int $unboundNumber
 * @property string $unboundString
 * @property bool $unboundBool
 * @property array $unboundArray
 * @property object $unboundObject
 * @property float $unboundFloat
 *
 * // Fluent methods
 *
 * @method self count(int $value)
 * @method int count()
 * @method string name()
 * @method self title(string $value)
 * @method self unboundString(string $value)
 * @method string unboundString()
 */
class Model
{
    use Properties;

    protected int $count = 0;

    protected bool $leaving = false;

    protected string $name = 'Axe';

    protected function repeatedName(int $count, string $name): string
    {
        return str_repeat($name, $count);
    }

    #[Property(aliases: ['greeting', 'howdy', 'hello'])]
    protected string $greeting = 'Hello, World!';

    protected string $farewell = 'Goodbye, World!';

    #[Property(onSet: ['strtoupper'])]
    protected string $title = '';

    protected string $slug = '';

    #[Property(onSet: ['json_encode'], onGet: ['json_decode'])]
    protected string $subtitle = '';

    protected function message(bool $leaving): string
    {
        return $leaving ? $this->farewell : $this->greeting;
    }

    #[Property(disableCache: true)]
    protected function uncachedName(): string
    {
        return $this->name;
    }
}

describe('Properties', function () {
    test('basic getters and setters', function () {
        $model = new Model();
        expect($model->name)->toBe('Axe');
        expect($model->leaving)->toBe(false);

        $model->name = 'Bear';
        $model->leaving = true;

        expect($model->name)->toBe('Bear');
        expect($model->leaving)->toBe(true);
    });

    test('calculated methods', function () {
        $model = new Model();

        $model->leaving = true;
        expect($model->message)->toBe('Goodbye, World!');

        $model->leaving = false;
        expect($model->message)->toBe('Hello, World!');

        $model->count = 3;
        $model->name = 'X';
        expect($model->repeatedName)->toBe('XXX');
    });

    test('access', function () {
        $model = new Model();

        $this->expectException(MagicException::class);
        $model->farewell;

        $this->expectException(MagicException::class);
        // @php-ignore
        $model->greeting = 'Hello, Axe!';
    });

    test('transformed properties', function () {
        $model = new Model();

        $model->title = 'axebear';
        expect($model->title)->toBe('AXEBEAR');

        $model->subtitle = ['key' => 'value'];
        expect($model->subtitle)->toBeInstanceOf(stdClass::class);
        expect($model->subtitle->key)->toBe('value');

        $rawSubtitle = $model->getRawValue('subtitle');
        expect($rawSubtitle)->toBe('{"key":"value"}');
    });

    test('uncached properties', function () {
        $model = new Model();
        $model->name = 'Axe';
        expect($model->uncachedName)->toBe('Axe');
        $model->name = 'Bear';
        expect($model->uncachedName)->toBe('Bear');
    });

    test('aliases', function () {
        $model = new Model();
        expect($model->greeting)->toBe($model->hello);
        expect($model->greeting)->toBe($model->howdy);
    });

    test('unbound properties', function () {
        $model = new Model();
        expect($model->unboundNumber)->toBeNull();
        $model->unboundNumber = 1;
        expect($model->unboundNumber)->toBe(1);
        expect($model->getRawValue('unboundNumber'))->toBe(1);
    });

    test('type conversion for unbound properties', function () {
        $model = new Model();

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

    test('fluent methods', function () {
        $model = new Model();
        $model->count(1)->count(2)->count(3);
        expect($model->count)->toBe(3);

        $model->count = 0;
        expect($model->count())->toBe(0);

        expect($model->name())->toBe('Axe');

        expect($model->unboundString())->toBeNull();
        expect($model->unboundString('AxeBear'))->toBeInstanceOf(Model::class);
        expect($model->unboundString())->toBe('AxeBear');

        $this->expectException(InvalidArgumentException::class);
        $model->name(1);

        $this->expectException(InvalidArgumentException::class);
        $model->name(1, 2);

        $this->expectException(InvalidArgumentException::class);
        $model->title;
    });
});

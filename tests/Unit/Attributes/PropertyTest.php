<?php

use AxeBear\Magic\Attributes\Property;
use AxeBear\Magic\Exceptions\MagicException;
use AxeBear\Magic\Traits\Properties;

/**
 * Sample class used for testing the #[Property] attribute and @property tag.
 *
 * @property bool $leaving
 * @property string $name
 * @property string $title
 * @property int $unbound
 * @property stdClass $subtitle
 * @property-read string $greeting
 * @property-read string $message
 * @property-write string $farewell
 */
class Model
{
    use Properties;

    protected bool $leaving = false;

    protected string $name = 'Axe';

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
}

describe('#[Property]', function () {
    test('basic properties', function () {
        $model = new Model();
        expect($model->name)->toBe('Axe');
        expect($model->leaving)->toBe(false);

        $model->name = 'Bear';
        $model->leaving = true;

        expect($model->name)->toBe('Bear');
        expect($model->leaving)->toBe(true);
    });

    test('methods as properties', function () {
        $model = new Model();

        $model->leaving = true;
        expect($model->message)->toBe('Goodbye, World!');

        $model->leaving = false;
        expect($model->message)->toBe('Hello, World!');
    });

    test('access', function () {
        $model = new Model();

        $this->expectException(MagicException::class);
        $model->farewell;

        $this->expectException(MagicException::class);
        // @php-ignore
        $model->greeting = 'Hello, Axe!';
    });

    test('transform properties', function () {
        $model = new Model();

        $model->title = 'axebear';
        expect($model->title)->toBe('AXEBEAR');

        $model->subtitle = ['key' => 'value'];
        expect($model->subtitle)->toBeInstanceOf(stdClass::class);
        expect($model->subtitle->key)->toBe('value');

        $rawSubtitle = $model->getRawValue('subtitle');
        expect($rawSubtitle)->toBe('{"key":"value"}');
    });

    test('aliases', function () {
        $model = new Model();
        expect($model->greeting)->toBe($model->hello);
        expect($model->greeting)->toBe($model->howdy);
    });

    test('unbound properties', function () {
        $model = new Model();
        expect($model->unbound)->toBeNull();
        $model->unbound = 1;
        expect($model->unbound)->toBe(1);
        expect($model->getRawValue('unbound'))->toBe(1);
    });
});

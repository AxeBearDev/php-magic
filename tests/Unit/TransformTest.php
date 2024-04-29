<?php

namespace Tests\Unit;

use AxeBear\Magic\Transform;
use AxeBear\Magic\Transforms;

describe('transform', function () {
    test('onSet', function () {
        class OnSetUser
        {
            use Transforms;

            #[Transform(onSet: ['trim', 'strtoupper'])]
            protected string $firstName;
        }

        $user = new OnSetUser();
        $user->firstName = ' j ';
        expect($user->firstName)->toBe('J');
        expect($user->getRaw('firstName'))->toBe('J');
    });

    test('onGet', function () {
        class OnGetUser
        {
            use Transforms;

            #[Transform(onGet: ['trim', 'strtoupper'])]
            protected string $firstName;
        }

        $user = new OnGetUser();
        $user->firstName = ' j ';
        expect($user->firstName)->toBe('J');
        expect($user->getRaw('firstName'))->toBe(' j ');
    });

    test('onSet and onGet', function () {
        /**
         * @property object $data
         */
        class OnSetAndGetUser
        {
            use Transforms;

            #[Transform(onSet: ['json_encode'], onGet: ['json_decode'])]
            protected $data;
        }

        $user = new OnSetAndGetUser();
        $user->data = ['name' => 'j'];
        expect($user->data->name)->toBe('j');
        expect($user->getRaw('data'))->toBe('{"name":"j"}');
    });

    test('exception for public properties', function () {
        $this->expectExceptionMessage('Properties with transforms must be protected or private.');
        $this->expectException(\AxeBear\Magic\MagicException::class);

        class PublicTransformUser
        {
            use Transforms;

            #[Transform(onSet: ['json_encode'], onGet: ['json_decode'])]
            public $data;
        }

        new PublicTransformUser();
    });
});

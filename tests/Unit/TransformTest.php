<?php

namespace Tests\Unit;

use AxeBear\Magic\Transform;
use AxeBear\Magic\Transforms;

describe('#[Transform]', function () {
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

    test('custom static callable', function () {
        class CustomCallableUser
        {
            use Transforms;

            #[Transform(onSet: ['addOne'], onGet: ['subtractOne'])]
            protected int $number;

            public static function addOne(int $value): int
            {
                return $value + 1;
            }

            public static function subtractOne(int $value): int
            {
                return $value - 1;
            }
        }

        $user = new CustomCallableUser();
        $user->number = 1;
        expect($user->number)->toBe(1);
        expect($user->getRaw('number'))->toBe(2);
    });

    test('custom instance callable', function () {
        class CustomInstanceCallableUser
        {
            use Transforms;

            public int $offset = 1;

            #[Transform(onGet: ['addOffset'])]
            protected int $number;

            public function addOffset(int $value): int
            {
                return $value + $this->offset;
            }
        }

        $user = new CustomInstanceCallableUser();
        $user->number = 1;
        expect($user->number)->toBe(2);
        expect($user->getRaw('number'))->toBe(1);

        $user->offset = 2;
        expect($user->number)->toBe(3);
        expect($user->getRaw('number'))->toBe(1);
    });

    test('exception for public properties', function () {
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

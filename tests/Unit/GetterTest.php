<?php

use AxeBear\Magic\Getter;
use AxeBear\Magic\Getters;
use AxeBear\Magic\MagicException;

describe('#[Getter]', function () {
    test('aliases', function () {
        /**
         * @property string $first_name
         * @property string $firstName
         */
        class AliasUser
        {
            use Getters;

            #[Getter(['first_name', 'firstName'])]
            public function firstName(): string
            {
                return 'Jean';
            }
        }

        $user = new AliasUser();
        expect($user->firstName)->toBe('Jean');
        expect($user->first_name)->toBe('Jean');
    });

    test('non-public getter methods', function () {
        /**
         * @property string $firstName
         */
        class NonPublicUser
        {
            use Getters;

            #[Getter]
            private function firstName(): string
            {
                return 'Jean';
            }
        }

        $this->expectException(MagicException::class);
        new NonPublicUser();
    });
});

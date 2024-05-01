<?php

use AxeBear\Magic\Attributes\Getter;
use AxeBear\Magic\Traits\Getters;

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
});

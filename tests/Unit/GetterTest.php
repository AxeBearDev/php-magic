<?php

use AxeBear\Magic\Getter;
use AxeBear\Magic\Getters;

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

    test('getter with parameters and caching', function () {
        class DependencyUser
        {
            use Getters;

            public string $firstName = 'Jean';

            public function getLastName(): string
            {
                return 'Doe';
            }

            #[Getter(useCache: true)]
            protected function fullName(string $firstName, string $getLastName): string
            {
                return $firstName.' '.$getLastName;
            }
        }

        $user = new DependencyUser();
        expect($user->fullName)->toBe('Jean Doe');

        $user->firstName = 'Jane';
        expect($user->fullName)->toBe('Jane Doe');
    });
});

<?php

use AxeBear\Magic\Compute;
use AxeBear\Magic\Computes;

describe('#[Compute]', function () {
    test('with parameters', function () {
        class DependencyUser
        {
            use Computes;

            public string $firstName = 'Jean';

            public function getLastName(): string
            {
                return 'Doe';
            }

            #[Compute(aliases: ['fullName'])]
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

    test('cache', function () {
        class CachedUser
        {
            use Computes;

            public int $counter = 0;

            #[Compute(useCache: true)]
            protected function increment(): int
            {
                $this->counter++;

                return $this->counter;
            }
        }

        $user = new CachedUser();
        expect($user->increment)->toBe(1);
        expect($user->counter)->toBe(1);

        $user->counter = 2;
        expect($user->increment)->toBe(1);
        expect($user->counter)->toBe(2);
    });
});

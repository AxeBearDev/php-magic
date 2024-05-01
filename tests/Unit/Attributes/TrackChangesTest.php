<?php

use AxeBear\Magic\Attributes\TrackChanges;
use AxeBear\Magic\Attributes\Transform;
use AxeBear\Magic\Traits\TracksChanges;
use AxeBear\Magic\Traits\Transforms;

describe('#[TrackChanges]', function () {
    test('class-level', function () {
        /**
         * @property string $lastName;
         */
        #[TrackChanges()]
        class VisibilityUser
        {
            use TracksChanges;

            public string $firstName = 'Jean';

            protected string $lastName = 'Doe';
        }

        $user = new VisibilityUser();
        $user->firstName = 'Jane';
        $user->lastName = 'Smith';

        expect($user->getTrackedChanges('lastName'))->toBe(['Doe', 'Smith']);
    });

    test('property-level', function () {
        class PropertyUser
        {
            use TracksChanges;

            public string $notTracked = 'Not tracked';

            #[TrackChanges]
            protected string $firstName = 'Jean';

            #[TrackChanges]
            protected string $lastName = 'Doe';

            public function changeLastName(string $lastName): void
            {
                $this->__set('lastName', $lastName);
            }
        }

        $user = new PropertyUser();
        $user->notTracked = 'Changed';
        $user->firstName = 'Jane';
        $user->lastName = 'Smith';

        expect($user->firstName)->toBe('Jane');
        expect($user->lastName)->toBe('Smith');
        expect($user->getTrackedChanges('notTracked'))->toBe(null);
        expect($user->getTrackedChanges('firstName'))->toBe(['Jean', 'Jane']);
        expect($user->getTrackedChanges('lastName'))->toBe(['Doe', 'Smith']);

        $user->changeLastName('Johnson');
        expect($user->lastName)->toBe('Johnson');
        expect($user->getTrackedChanges('lastName'))->toBe(['Doe', 'Smith', 'Johnson']);
    });

    test('rollback', function () {
        #[TrackChanges]
        class RollbackUser
        {
            use TracksChanges;

            protected string $firstName = 'Jean';

            protected string $lastName = 'Doe';
        }

        $user = new RollbackUser();
        $user->firstName = 'Jane';
        $user->lastName = 'Smith';

        expect($user->firstName)->toBe('Jane');
        expect($user->lastName)->toBe('Smith');

        $user->rollbackChanges('firstName');
        expect($user->firstName)->toBe('Jean');
        expect($user->lastName)->toBe('Smith');

        $user->rollbackChanges();
        expect($user->firstName)->toBe('Jean');
        expect($user->lastName)->toBe('Doe');
    });

    test('track with transform', function () {
        #[TrackChanges]
        class TransformUser
        {
            use Transforms;
            use TracksChanges;

            #[Transform(onSet: ['strtoupper'])]
            protected string $firstName = 'Jean';

            #[Transform(onGet: ['strtoupper'])]
            protected string $lastName = 'Doe';
        }

        $user = new TransformUser();
        $user->firstName = 'Jane';
        $user->lastName = 'Smith';

        expect($user->firstName)->toBe('JANE');
        expect($user->lastName)->toBe('SMITH');
        expect($user->getTrackedChanges('firstName'))->toBe(['Jean', 'JANE']);
        expect($user->getTrackedChanges('lastName'))->toBe(['Doe', 'Smith']);
    });
});

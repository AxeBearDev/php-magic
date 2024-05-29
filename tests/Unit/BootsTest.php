<?php

use AxeBear\Magic\Attributes\Booter;
use AxeBear\Magic\Traits\Boots;

class BootsTest
{
    use Boots;

    public array $booted = [];

    #[Booter]
    protected function bootLast()
    {
        $this->booted[] = 'last';
    }

    #[Booter(1)]
    protected function bootMiddle()
    {
        $this->booted[] = 'middle';
    }

    #[Booter(2)]
    protected function bootFirst()
    {
        $this->booted[] = 'first';
    }
}

class BootsTestChild extends BootsTest
{
    #[Booter(3)]
    protected function bootChild()
    {
        $this->booted[] = 'child';
    }
}

test('sequencing', function () {
    $model = new BootsTest();
    expect($model->booted)->toBe(['first', 'middle', 'last']);
});

test('child sequencing', function () {
    $model = new BootsTestChild();
    expect($model->booted)->toBe(['child', 'first', 'middle', 'last']);
});

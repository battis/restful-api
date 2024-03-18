<?php

namespace Battis\CRUD\Tests\Fixtures\SpecTest;

use Battis\CRUD\Spec;

class SpecFixture extends Spec
{
    public $options;

    protected function constructorHook(array $options = [])
    {
        $this->options = $options;
    }
}

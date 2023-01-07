<?php

namespace Battis\CRUD\Tests\Fixtures\Traits\Record\SpecDefinitionsTest;

use Battis\CRUD\Spec;

class SpecSubclass extends Spec {
    private $options;

    protected function constructorHook(array $options = [])
    {
        $this->options = $options;
    }

    public function getOptions() {
        return $this->options;
    }
}

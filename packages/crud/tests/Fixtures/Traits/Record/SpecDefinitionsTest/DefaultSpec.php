<?php

namespace Battis\CRUD\Tests\Fixtures\Traits\Record\SpecDefinitionsTest;

use Battis\CRUD\Record;
use Battis\CRUD\Traits\Record\SpecDefinitions;

class DefaultSpec extends Record {
    use SpecDefinitions;

    public static function fixtureDefineSpec() {
        return static::defineSpec();
    }
}

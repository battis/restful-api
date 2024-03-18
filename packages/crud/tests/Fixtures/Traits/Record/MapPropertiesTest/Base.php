<?php

namespace Battis\CRUD\Tests\Fixtures\Traits\Record\MapPropertiesTest;

use Battis\CRUD\Record;
use Battis\CRUD\Traits\Record\MapProperties;

class Base extends Record {
    use MapProperties;

    protected $prop1;

    protected static function definePropertyToFieldMapping(): array
    {
        return ['prop1' => 'field1'];
    }
}

<?php

namespace Battis\CRUD\Tests\Fixtures\Traits\Spec\MapPropertiesTest;

use Battis\CRUD\Record;
use Battis\CRUD\Traits\Record\MapProperties;

class MappedRecordFixture extends Record
{
    use MapProperties;

    public $mappedProp1;
    public $mappedProp2;
    public $unmappedProp;

    protected static function definePropertyToFieldMapping(): array
    {
        return [
            'mappedProp1' => 'mapped_field_1',
            'mappedProp2' => 'mapped_field_2'
        ];
    }

    public function fixtureGetSpec()
    {
        return $this->getSpec();
    }
}

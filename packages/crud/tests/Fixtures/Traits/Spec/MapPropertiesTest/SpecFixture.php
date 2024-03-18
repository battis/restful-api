<?php

namespace Battis\CRUD\Tests\Fixtures\Traits\Spec\MapPropertiesTest;

use Battis\CRUD\Spec;
use Battis\CRUD\Traits\Spec\MapProperties;

class SpecFixture extends Spec
{
    use MapProperties;

    public function getPropertyToFieldMap()
    {
        return $this->propertyToFieldMap;
    }
}

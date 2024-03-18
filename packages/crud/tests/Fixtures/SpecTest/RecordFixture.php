<?php

namespace Battis\CRUD\Tests\Fixtures\SpecTest;

use Battis\CRUD\Record;

class RecordFixture extends Record
{
    private $prop1;
    private $prop2;

    public function setProp1($value)
    {
        $this->prop1 = $value;
    }

    public function getProp1()
    {
        return $this->prop1;
    }

    protected function setProp2($value)
    {
        $this->prop2 = $value;
    }

    public function getProp2()
    {
        return $this->prop2;
    }
}

<?php

namespace Tests\Battis\CRUD\fixtures;

class RecordWithSetters extends RecordWithFields
{
    public function setFoo($value)
    {
        $this->foo = $value;
    }
}

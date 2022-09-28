<?php

namespace Tests\Battis\CRUD\fixtures;

class RecordWithSetters extends RecordWithFields
{
    protected $foo_bar_baz;
    protected $argleBargle;

    public function setFoo($value)
    {
        $this->foo = $value;
    }

    public function setFooBarBaz($value)
    {
        $this->foo_bar_baz = $value;
    }

    public function setArgleBargle($value)
    {
        $this->argleBargle = $value;
    }
}

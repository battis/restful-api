<?php

namespace Test\Battis\CRUD;

use Tests\Battis\CRUD\fixtures\RecordWithSetters;

class RecordWithSettersTest extends RecordWithFieldsTest
{
    protected function getType(): string
    {
        return RecordWithSetters::class;
    }

    protected function getTableName(): string
    {
        return "record_with_setterses";
    }
}

<?php

namespace Tests\Battis\CRUD;

use Tests\Battis\CRUD\fixtures\RecordWithSetters;

class RecordWithSettersMySQLTest extends RecordWithFieldsMySQLTest
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

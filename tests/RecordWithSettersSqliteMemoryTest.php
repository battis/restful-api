<?php

namespace Tests\Battis\CRUD;

use Tests\Battis\CRUD\fixtures\RecordWithSetters;

abstract class RecordWithSettersSqliteMemoryTest extends
    RecordWithFieldsSqliteMemoryTest
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

<?php

namespace Tests\Battis\CRUD;

use Tests\Battis\CRUD\fixtures\RecordWithSetters;
use Tests\Battis\CRUD\RecordWithFieldsSqliteFileTest;

class RecordWithSetterSqliteFileTest extends RecordWithFieldsSqliteFileTest
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

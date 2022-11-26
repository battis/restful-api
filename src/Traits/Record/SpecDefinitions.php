<?php

namespace Battis\CRUD\Traits\Record;

trait SpecDefinitions
{
    protected static function defineTableName(): ?string
    {
        return null;
    }

    protected static function definePrimaryKey(): ?string
    {
        return null;
    }

    protected static function defineOptions(): array
    {
        return [];
    }
}

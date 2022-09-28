<?php

namespace Battis\CRUD\RecordTraits;

trait MapProperties
{
    protected static function objectToDatabaseHook(array $data): array
    {
        $s = static::getSpec();
        return $s->mapPropertiesToFields($data);
    }

    protected static function databaseToObjectHook(array $data): array
    {
        $s = static::getSpec();
        return $s->mapFieldsToProperties($data);
    }
}

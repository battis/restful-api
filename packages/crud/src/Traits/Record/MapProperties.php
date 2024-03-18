<?php

namespace Battis\CRUD\Traits\Record;

use Battis\CRUD\Spec;
use Battis\CRUD\Traits\MapProperties as MapPropertiesConstant;
use Battis\CRUD\Traits\Record\SpecDefinitions;
use Battis\CRUD\Traits\Spec\MapProperties as SpecMapProperties;

trait MapProperties
{
    use SpecDefinitions, MapPropertiesConstant;

    protected static function defineSpec(): Spec
    {
        return new class(
            static::class,
            static::defineTableName(),
            static::definePrimaryKey(),
            array_merge(
                static::defineOptions(),
                [
                    self::$MAPPING => static::definePropertyToFieldMapping()
                ]
            )
        ) extends Spec {
            use SpecMapProperties;
        };
    }

    abstract protected static function definePropertyToFieldMapping(): array;

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

<?php

namespace Battis\CRUD\Traits\Record;

use Battis\CRUD\Spec;

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

    protected static function defineSpec(): Spec
    {
        return new Spec(static::class, static::defineTableName(), static::definePrimaryKey(), static::defineOptions());
    }
}

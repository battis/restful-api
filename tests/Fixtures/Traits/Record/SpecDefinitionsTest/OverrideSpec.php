<?php

namespace Battis\CRUD\Tests\Fixtures\Traits\Record\SpecDefinitionsTest;

use Battis\CRUD\Spec;

class OverrideSpec extends DefaultSpec
{
    private $options;

    protected static function defineTableName(): ?string
    {
        return 'override';
    }

    protected static function definePrimaryKey(): ?string
    {
        return 'override_id';
    }

    protected static function defineOptions(): array
    {
        return ['foo'=>'bar'];
    }

    protected static function defineSpec(): Spec
    {
        return new SpecSubclass(static::class, static::defineTableName(), static::definePrimaryKey(), static::defineOptions());
    }
}

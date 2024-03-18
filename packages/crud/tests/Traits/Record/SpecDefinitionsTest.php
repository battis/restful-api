<?php

namespace Battis\CRUD\Tests\Traits\Record;

use Battis\CRUD\Tests\Fixtures\Traits\Record\SpecDefinitionsTest\DefaultSpec;
use Battis\CRUD\Tests\Fixtures\Traits\Record\SpecDefinitionsTest\OverrideSpec;
use Battis\CRUD\Tests\Fixtures\Traits\Record\SpecDefinitionsTest\SpecSubclass;
use PHPUnit\Framework\TestCase;

class SpecDefinitionsTest extends TestCase {
    public function testDefaults()
    {
        $default = new DefaultSpec();
        $spec = $default->fixtureDefineSpec();
        $this->assertEquals('default_specs', $spec->getTableName());
        $this->assertEquals('id', $spec->getPrimaryKey());
    }

    public function testOverrides()
    {
        $override = new OverrideSpec();
        /** @var SpecSubclass $spec */
        $spec = $override->fixtureDefineSpec();
        $this->assertEquals('override', $spec->getTableName());
        $this->assertEquals('override_id', $spec->getPrimaryKey());
        $this->assertEquals(['foo' => 'bar'], $spec->getOptions());
    }
}

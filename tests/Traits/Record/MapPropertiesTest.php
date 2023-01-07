<?php

namespace Battis\CRUD\Tests\Traits\Record;

use Battis\CRUD\Tests\TestCase;

class MapPropertiesTest extends TestCase {
    protected function setUp(): void
    {
        $this->getPDO()->query(file_get_contents($this->getPathToFixture('record_fixtures.sql')));
        parent::setUp();
    }

    public function getDataset()
    {
        return $this->getCsvDataSet('record_fixtures');
    }
}

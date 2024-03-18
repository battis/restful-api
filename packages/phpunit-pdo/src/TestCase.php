<?php

namespace Battis\PHPUnit\PDO;

use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableContainsRow;
use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableExists;
use Battis\PHPUnit\PDO\Traits\Fixture;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    use Fixture;

    use AssertTableExists, AssertTableContainsRow;

    protected function setUp(): void
    {
        $this->getFixture()->setUp($this->getPDO());
    }

    protected function tearDown(): void
    {
        $this->getFixture()->tearDown($this->getPDO());
    }

    public static function tearDownAfterClass(): void
    {
        static::destroyPDO();
    }
}

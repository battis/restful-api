<?php

namespace Battis\PHPUnit\PDO\Tests\Traits;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableExists;
use Battis\PHPUnit\PDO\Fixture\Fixture;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Tests\Fixtures\Traits\FixtureTest\FixtureFixture;
use PHPUnit\Framework\TestCase;

class FixtureTest extends TestCase
{
    use FixturePath, AssertTableExists;

    public function testFixture(): void
    {
        $fixture = Fixture::fromYamlFile(
            $this->getPathToFixture('fixture.yaml')
        )->withSchema(
            Query::fromSqlFile($this->getPathToFixture('schema.sql'))
        );

        $fixtureObj = new FixtureFixture(
            fn(string $fileName) => $this->getPathToFixture($fileName)
        );
        $this->assertNull($fixtureObj->fixtureGetFixture());

        $fixtureObj->getFixture()->setUp($fixtureObj->getPDO());
        $this->assertNotNull($fixtureObj->fixtureGetPDO());
        $this->assertEquals($fixture, $fixtureObj->fixtureGetFixture());
        $this->assertEquals($fixture, $fixtureObj->fixtureGetFixture());
        foreach ($fixture->getTables() as $table) {
            $this->assertTableExists($table, $fixtureObj->getPDO());
        }

        $fixtureObj->getFixture()->tearDown($fixtureObj->getPDO());
        foreach ($fixture->getTables() as $table) {
            $this->assertTableDoesNotExist($table, $fixtureObj->getPDO());
        }
    }
}

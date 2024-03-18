<?php

namespace Battis\PHPUnit\PDO\Tests\Fixture;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableContainsRow;
use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableExists;
use Battis\PHPUnit\PDO\Exceptions\SchemaException;
use Battis\PHPUnit\PDO\Fixture\Fixture;
use Battis\PHPUnit\PDO\Fixture\Table;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Traits\PDO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class FixtureTest extends TestCase
{
    use FixturePath, PDO, AssertTableExists, AssertTableContainsRow;

    private ?array $array = null;
    private ?Query $schema = null;
    private ?Fixture $fixture = null;

    protected function setUp(): void
    {
        $this->array = null;
        $this->schema = null;
        $this->fixture = null;
    }

    /**
     * @return array<tables: array{name: string, rows: array<int, array<string, mixed>>}>
     */
    private function getArray(): array
    {
        if (!$this->array) {
            $this->array = Yaml::parseFile(
                $this->getPathToFixture('fixture.yaml')
            );
        }
        return $this->array;
    }

    private function getSchema(): Query
    {
        if (!$this->schema) {
            $this->schema = Query::fromSqlFile(
                $this->getPathToFixture('schema.sql')
            );
        }
        return $this->schema;
    }

    private function getFixture(): Fixture
    {
        if (!$this->fixture) {
            $this->fixture = Fixture::fromArray($this->getArray());
        }
        return $this->fixture;
    }

    private function testFromArray(): void
    {
        foreach (
            array_values($this->getFixture()->getTables())
            as $i => $table
        ) {
            $this->assertEquals(
                Table::fromArray(
                    $this->getArray()[Fixture::TABLES][$i]
                )->inFixture($this->getFixture()),
                $table
            );
        }
        foreach ($this->getArray()[Fixture::TABLES] as $i => $tableArray) {
            $table = Table::fromArray($tableArray)->inFixture(
                $this->getFixture()
            );
            $this->assertEquals($table, $this->getFixture()[$table->getName()]);
        }
    }

    public function testSetUp(): void
    {
        try {
            $this->getFixture()->setUp($this->getPDO());
            $this->assertTrue(false);
        } catch (SchemaException $e) {
            $this->assertTrue(true);
        }

        $this->fixture = $this->getFixture()->withSchema($this->getSchema());
        foreach ($this->getFixture()->getTables() as $table) {
            $this->assertTableDoesNotExist($table, $this->getPDO());
        }
        $this->getFixture()->setUp($this->getPDO());
        foreach ($this->getFixture()->getTables() as $table) {
            $this->assertTableExists($table, $this->getPDO());
            foreach ($table->getRows() as $row) {
                $this->assertTableContainsRow($row, $table, $this->getPDO());
            }
        }
    }

    public function testTearDown(): void
    {
        $this->fixture = $this->getFixture()->withSchema($this->getSchema());
        $this->getFixture()->setUp($this->getPDO());
        foreach ($this->getFixture()->getTables() as $table) {
            $this->assertTableExists($table, $this->getPDO());
        }
        $this->getFixture()->tearDown($this->getPDO());
        foreach ($this->getFixture() as $table) {
            $this->assertTableDoesNotExist($table, $this->getPDO());
        }
    }

    public function testEquals(): void
    {
        $this->assertTrue(
            Fixture::fromArray($this->getArray())->equals($this->getFixture())
        );
        $this->assertFalse(
            $this->getFixture()->equals(
                Table::fromArray($this->getArray()[Fixture::TABLES][0])
            )
        );
        $array = $this->getArray();
        $array[Fixture::TABLES][2][Table::ROWS][0]['id'] += 500;
        $this->assertFalse(
            $this->getFixture()->equals(Fixture::fromArray($array))
        );
        unset($array[Fixture::TABLES][2]);
        $this->assertFalse(
            Fixture::fromArray($array)->equals($this->getFixture())
        );
    }

    public function testGetTableNames(): void
    {
        $this->assertEquals(
            array_map(
                fn(array $a): string => $a[Table::NAME],
                $this->getArray()[Fixture::TABLES]
            ),
            $this->getFixture()->getTableNames()
        );
    }
}

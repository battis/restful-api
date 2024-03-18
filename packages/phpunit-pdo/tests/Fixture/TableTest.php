<?php

namespace Battis\PHPUnit\PDO\Tests\Fixture;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableContainsRow;
use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableExists;
use Battis\PHPUnit\PDO\Fixture\Fixture;
use Battis\PHPUnit\PDO\Fixture\Row;
use Battis\PHPUnit\PDO\Fixture\Table;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Traits\PDO;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class TableTest extends TestCase
{
    use FixturePath, PDO, AssertTableExists, AssertTableContainsRow;

    private ?array $array = null;
    private ?Table $table = null;
    private ?Query $schema = null;
    private ?Fixture $fixture = null;

    protected function setUp(): void
    {
        $this->array = null;
        $this->schema = null;
        $this->table = null;
        $this->fixture = null;
    }

    /**
     * @return array{name: string, rows: array<int, array<string, mixed>>}
     */
    private function getArray(): array
    {
        if (!$this->array) {
            $this->array = Yaml::parseFile(
                $this->getPathToFixture('table.yaml')
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

    private function getTable(): Table
    {
        if (!$this->table) {
            $this->table = Table::fromArray($this->getArray());
        }
        return $this->table;
    }

    private function getFixture(): Fixture
    {
        if (!$this->fixture) {
            $this->fixture = Fixture::fromYamlFile(
                $this->getPathToFixture('fixture.yaml')
            )->withSchema(
                Query::fromSqlFile($this->getPathToFixture('fixture.sql'))
            );
        }
        return $this->fixture;
    }

    public function testFromArray(): void
    {
        $this->assertEquals(
            $this->getArray()[Table::NAME],
            $this->getTable()->getName()
        );
        foreach ($this->getTable()->getRows() as $i => $row) {
            $this->assertEquals(
                Row::fromArray($this->getArray()[Table::ROWS][$i])->inTable(
                    $this->getTable()
                ),
                $row
            );
        }
        foreach ($this->getArray()[Table::ROWS] as $i => $rowArray) {
            $this->assertEquals($rowArray, $this->getTable()[$i]->getValues());
        }
    }

    public function testSetup(): void
    {
        $this->assertTableDoesNotExist($this->getTable(), $this->getPDO());
        $this->table = $this->getTable()->withSchema($this->getSchema());
        $this->getTable()->setUp($this->getPDO());
        $this->assertTableExists($this->getTable(), $this->getPDO());

        foreach ($this->getFixture()->getTables() as $table) {
            $this->assertTableDoesNotExist($table, $this->getPDO());
            $this->expectException(Exception::class);
            $table->setUp($this->getPDO());
        }
    }

    public function testTearDown(): void
    {
        $this->table = $this->getTable()->withSchema($this->getSchema());
        $this->getTable()->setUp($this->getPDO());
        $this->assertTableExists($this->getTable(), $this->getPDO());
        $this->getTable()->tearDown($this->getPDO());
        $this->assertTableDoesNotExist($this->getTable(), $this->getPDO());
    }

    public function testInsertAll(): void
    {
        $this->table = $this->getTable()->withSchema($this->getSchema());
        $this->getTable()->createIn($this->getPDO());
        foreach ($this->getTable()->getRows() as $row) {
            $this->assertTableDoesNotContainRow(
                $row,
                $this->getTable(),
                $this->getPDO()
            );
        }
        $this->getTable()->insertAll($this->getPDO());
        foreach ($this->getTable()->getRows() as $row) {
            $this->assertTableContainsRow(
                $row,
                $this->getTable(),
                $this->getPDO()
            );
        }
        foreach (
            Query::selectAllFrom($this->getTable())->fetchAllFrom(
                $this->getPDO()
            )
            as $rowArray
        ) {
            $this->assertTrue(
                in_array($rowArray, $this->getArray()[Table::ROWS])
            );
        }
    }

    public function testEquals(): void
    {
        $this->assertTrue(
            $this->getTable()->equals(Table::fromArray($this->getArray()))
        );
        $array = $this->getArray();
        $array[Table::ROWS][0]['id'] = $array[Table::ROWS][0]['id'] - 100;
        $this->assertFalse($this->getTable()->equals(Table::fromArray($array)));
        foreach ($this->getFixture()->getTables() as $table) {
            $this->assertFalse($table->equals($this->getTable()));
        }
    }

    public function testGetColumnNames(): void
    {
        $this->assertEquals(
            array_keys($this->getArray()[Table::ROWS][0]),
            $this->getTable()->getColumnNames()
        );
        $this->assertEquals(
            [],
            Table::fromArray([
                Table::NAME => 'empty',
                Table::ROWS => [],
            ])->getColumnNames()
        );
    }
}

<?php

namespace Battis\PHPUnit\PDO\Tests\Fixture;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableContainsRow;
use Battis\PHPUnit\PDO\Exceptions\RowException;
use Battis\PHPUnit\PDO\Fixture\Row;
use Battis\PHPUnit\PDO\Fixture\Table;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Traits\PDO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class RowTest extends TestCase
{
    use FixturePath, PDO, AssertTableContainsRow;

    private ?array $array = null;

    private ?Row $row = null;

    private ?Table $table = null;

    protected function setUp(): void
    {
        $this->array = null;
        $this->row = null;
        $this->table = null;
    }

    private function getArray(): array
    {
        if (!$this->array) {
            $this->array = Yaml::parseFile($this->getPathToFixture('row.yaml'));
        }
        return $this->array;
    }

    private function getRow(): Row
    {
        if (!$this->row) {
            $this->row = Row::fromArray($this->getArray());
        }
        return $this->row;
    }

    private function getTable(): Table
    {
        if (!$this->table) {
            $this->table = Table::fromYamlFile(
                $this->getPathToFixture('table.yaml')
            )->withSchema(
                Query::fromSqlFile($this->getPathToFixture('schema.sql'))
            );
            $this->table->createIn($this->getPDO());
        }
        return $this->table;
    }

    protected function tearDown(): void
    {
        $this->getTable()->tearDown($this->getPDO());
    }

    public function testFromArray(): void
    {
        $this->assertEquals($this->getArray(), $this->getRow()->getValues());
    }

    public function testHookGetValue(): void
    {
        foreach ($this->getArray() as $key => $value) {
            $this->assertEquals($value, $this->getRow()[$key]);
        }
    }

    public function testInTable(): void
    {
        $this->getRow()->inTable($this->getTable());
        $this->assertTableDoesNotContainRow(
            $this->getRow(),
            $this->getTable(),
            $this->getPDO()
        );
        $this->getRow()->insertInto($this->getPDO());
        $this->assertTableContainsRow(
            $this->getRow(),
            $this->getTable(),
            $this->getPDO()
        );
    }

    public function testInsertInto(): void
    {
        foreach ($this->getTable()->getRows() as $row) {
            $this->assertTableDoesNotContainRow(
                $row,
                $this->getTable(),
                $this->getPDO()
            );
            $row->insertInto($this->getPDO());
            $this->assertTableContainsRow(
                $row,
                $this->getTable(),
                $this->getPDO()
            );
        }

        $this->expectException(RowException::class);
        $this->getRow()->insertInto($this->getPDO());
    }

    public function testEquals(): void
    {
        foreach ($this->getTable()->getRows() as $row) {
            $this->assertFalse($row->equals($this->getRow()));
        }

        $this->assertTrue(
            $this->getRow()->equals(Row::fromArray($this->getArray()))
        );

        $this->assertFalse($this->getRow()->equals($this->getTable()));
    }

    public function testGetColumnNames(): void
    {
        $this->assertEquals(
            array_keys($this->getArray()),
            $this->getRow()->getColumnNames()
        );
    }

    public function testGetValues(): void
    {
        $this->assertEquals($this->getArray(), $this->getRow()->getValues());
    }
}

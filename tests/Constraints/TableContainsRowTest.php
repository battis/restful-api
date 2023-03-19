<?php

namespace Battis\PHPUnit\PDO\Tests\Constraints;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Constraints\TableContainsRow;
use Battis\PHPUnit\PDO\Fixture\Table;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Traits\PDO;

/**
 * @extends ConstraintTestCase<TableContainsRow>
 */
class TableContainsRowTest extends ConstraintTestCase
{
    use FixturePath, PDO;

    private ?Table $table = null;

    protected function setUp(): void
    {
    }

    private function getTable(): Table
    {
        if (!$this->table) {
            $this->table = Table::fromYamlFile(
                $this->getPathToFixture('table.yaml')
            )->withSchema(
                Query::fromSqlFile($this->getPathToFixture('table.sql'))
            );
        }
        return $this->table;
    }

    protected function getConstraint(): TableContainsRow
    {
        if (!$this->constraint) {
            $this->constraint = new TableContainsRow(
                $this->getTable(),
                $this->getPDO()
            );
        }
        return $this->constraint;
    }

    public function testMatches(): void
    {
        $this->getTable()->setUp($this->getPDO());

        $delete = Query::deleteFrom($this->getTable())->prepare(
            $this->getPDO()
        );
        foreach ($this->getTable()->getRows() as $expected) {
            $this->assertTrue($this->getConstraint()->matches($expected));
            $delete->execute(['id' => $expected['id']]);
            $this->assertFalse($this->getConstraint()->matches($expected));
        }
    }
}

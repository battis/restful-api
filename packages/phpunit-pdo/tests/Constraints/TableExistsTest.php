<?php

namespace Battis\PHPUnit\PDO\Tests\Constraints;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Constraints\TableExists;
use Battis\PHPUnit\PDO\Fixture\Table;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Traits\PDO;

/**
 * @extends ConstraintTestCase<TableExists>
 */
class TableExistsTest extends ConstraintTestCase
{
    use FixturePath, PDO;

    private ?Table $table = null;

    protected function setUp(): void
    {
        $this->table = null;
        $this->constraint = null;
    }

    private function getTable()
    {
        if (!$this->table) {
            $this->table = Table::fromYamlFile(
                $this->getPathToFixture('fixture.yaml')
            )->withSchema(
                Query::fromSqlFile($this->getPathToFixture('schema.sql'))
            );
        }
        return $this->table;
    }

    protected function getConstraint(): TableExists
    {
        if (!$this->constraint) {
            $this->constraint = new TableExists($this->getPDO());
        }
        return $this->constraint;
    }

    public function testMatches(): void
    {
        $this->assertFalse($this->getConstraint()->matches($this->getTable()));
        $this->getTable()->setUp($this->getPDO());
        $this->assertTrue($this->getConstraint()->matches($this->getTable()));
    }
}

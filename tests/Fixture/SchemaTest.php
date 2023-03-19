<?php

namespace Battis\PHPUnit\PDO\Tests\Fixture;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Constraints\Assertions\AssertTableExists;
use Battis\PHPUnit\PDO\Exceptions\SchemaException;
use Battis\PHPUnit\PDO\Fixture\Table;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Tests\Fixtures\Fixture\SchemaTest\SchemaObject;
use Battis\PHPUnit\PDO\Traits\PDO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class SchemaTest extends TestCase
{
    use FixturePath, PDO, AssertTableExists;

    private ?array $array = null;

    private ?Query $schema = null;

    private ?SchemaObject $schemaObj = null;

    private ?Table $table = null;

    protected function setUp(): void
    {
        $this->array = null;
        $this->schema = null;
        $this->table = null;
        $this->schemaObj = null;
    }

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

    private function getTable(): Table
    {
        if (!$this->table) {
            $this->table = Table::fromArray($this->getArray())->withSchema(
                $this->getSchema()
            );
        }
        return $this->table;
    }

    private function getSchemaObject(): SchemaObject
    {
        if (!$this->schemaObj) {
            $this->schemaObj = SchemaObject::fromArray(
                $this->getArray()[Table::ROWS][0]
            );
        }
        return $this->schemaObj;
    }

    public function testWithSchema(): void
    {
        $withSchema = $this->getSchemaObject()->withSchema($this->getSchema());
        $this->assertSame($this->schemaObj, $withSchema);
    }

    public function testGetSchema(): void
    {
        $this->assertEquals(
            $this->getSchema(),
            $this->getSchemaObject()
                ->withSchema($this->getSchema())
                ->getSchema()
        );
    }

    public function testCreateIn(): void
    {
        $this->getTable()->createIn($this->getPDO());
        $this->assertTableExists($this->getTable(), $this->getPDO());
    }

    public function testCreateInWithoutSChema(): void
    {
        $this->expectException(SchemaException::class);
        $this->getSchemaObject()->createIn($this->getPDO());
    }
}

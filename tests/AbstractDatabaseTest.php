<?php

namespace Tests\Battis\CRUD;

use Envms\FluentPDO\Query;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * @backupStaticAttributes enabled
 */
abstract class AbstractDatabaseTest extends TestCase
{
    /** @var PDO */
    private $pdo;

    /** @var Query[] */
    private $queries = [];

    protected function setPDO(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function getPDO(): PDO
    {
        return $this->pdo;
    }

    protected function setUp(): void
    {
        if (!$this->pdo) {
            $this->pdo = new PDO($this->getDSN());
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->queries as $query) {
            $query->close();
        }
        $this->pdo = null;
    }

    protected function query(): Query
    {
        $query = new Query($this->getPDO());
        $this->queries[] = $query;
        return $query;
    }

    protected function getDSN()
    {
        return "sqlite::memory:";
    }

    abstract protected function getTableName(): string;

    protected function getPrimaryKey(): string
    {
        return "id";
    }

    protected function insertRow(array $data)
    {
        $this->query()
            ->insertInto($this->getTableName())
            ->values($data)
            ->execute();
    }

    protected function insertRows(array $data)
    {
        foreach ($data as $datum) {
            $this->insertRow($datum);
        }
    }

    private function primaryKeyEquals($id)
    {
        return ["`" . $this->getPrimaryKey() . "` = ?", $id];
    }

    protected function deleteRow($id)
    {
        $this->query()
            ->delete($this->getTableName())
            ->where(...$this->primaryKeyEquals($id))
            ->execute();
    }

    public function assertDatabaseRowExists($id, $message = "")
    {
        $this->assertNotFalse(
            $this->query()
                ->from($this->getTableName())
                ->where(...$this->primaryKeyEquals($id))
                ->fetch(),
            $message
        );
    }

    public function asssertDatabaseRowDoesNotExist($id, $message = "")
    {
        $this->assertFalse(
            $this->query()
                ->from($this->getTableName())
                ->where(...$this->primaryKeyEquals($id))
                ->fetch(),
            $message
        );
    }

    public function assertDatabaseRowMatch(array $data, $message = "")
    {
        $query = $this->query()->from($this->getTableName());
        foreach ($data as $key => $value) {
            $query = $query->where("`$key` = ?", $value);
        }
        $this->assertNotFalse($query->fetch(), $message);
    }

    public function assertDatabaseRowDoesNotMatch(array $data, $message = "")
    {
        $query = $this->query()->from($this->getTableName());
        foreach ($data as $key => $value) {
            $query = $query->where("`$key` = ?", $value);
        }
        $this->assertFalse($query->fetch(), $message);
    }

    public function assertDatabaseExactRowExists(array $data, $message = "")
    {
        $query = $this->query()->from($this->getTableName());
        foreach ($data as $key => $value) {
            $query = $query->where("`$key` = ?", $value);
        }
        $this->assertNotFalse($row = $query->fetch(), $message);

        foreach ($row as $key => $value) {
            $this->assertEquals($data[$key], $value, $message);
        }
        foreach ($data as $key => $value) {
            $this->assertEquals($row[$key], $value, $message);
        }
    }

    public function assertDatabaseExactRowDoesNotExist(
        array $data,
        $message = ""
    ) {
        $query = $this->query()->from($this->getTableName());
        foreach ($data as $key => $value) {
            $query = $query->where("`$key` = ?", $value);
        }
        $row = $query->fetch();
        if ($row == false) {
            $this->assertFalse($row, $message);
        } else {
            $this->assertFalse(
                array_reduce(
                    $row,
                    function ($state, $value) use ($data) {
                        return $state && in_array($value, array_keys($data));
                    },
                    true
                ),
                $message
            );
        }
    }

    public function assertDatabaseTableEquals(array $data, $message = "")
    {
        $table = $this->query()
            ->from($this->getTableName())
            ->fetchAll();
        $this->assertEquals(count($data), count($table), $message);
        foreach ($table as $row) {
            $matched = false;
            foreach ($data as $datum) {
                if (count($datum) == count($row)) {
                    $complete = true;
                    foreach (array_keys($datum) as $key) {
                        if ($row[$key] != $datum[$key]) {
                            $complete = false;
                            break;
                        }
                    }
                    if ($complete) {
                        $matched = true;
                        break;
                    }
                }
            }
            $this->assertTrue($matched, $message);
        }
    }
}

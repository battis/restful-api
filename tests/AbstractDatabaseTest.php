<?php

namespace Tests\Battis\CRUD;

use Battis\CRUD\Connection;
use Envms\FluentPDO\Query;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * @backupStaticAttributes enabled
 */
abstract class AbstractDatabaseTest extends TestCase
{
    private $pdo;

    protected function getPDO(): PDO
    {
        if (!$this->pdo) {
            $this->pdo = new PDO("sqlite::memory:");
        }
        return $this->pdo;
    }

    protected function setUp(): void
    {
        Connection::getInstance($this->getPDO());
        $this->getPDO()->query($this->getSetupSQL());
    }

    abstract protected function getSetupSQL(): string;

    protected function tearDown(): void
    {
        $this->getPDO()->query($this->getTearDownSQL());
    }

    abstract protected function getTearDownSQL(): string;

    protected function query(): Query
    {
        return new Query($this->getPDO());
    }

    abstract protected function getTableName(): string;

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

    protected function deleteRow($id)
    {
        $this->query()
            ->delete($this->getTableName(), $id)
            ->execute();
    }

    public function assertDatabaseRowExists($id, $message = "")
    {
        $this->assertNotFalse(
            $this->query()
                ->from($this->getTableName(), $id)
                ->fetch(),
            $message
        );
    }

    public function asssertDatabaseRowDoesNotExist($id, $message = "")
    {
        $this->assertFalse(
            $this->query()
                ->from($this->getTableName(), $id)
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

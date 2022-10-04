<?php

namespace Tests\Battis\CRUD;

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
        $statement = $this->getPDO()->prepare(
            "INSERT INTO " .
                $this->getTableName() .
                " (" .
                join(",", array_keys($data)) .
                ") VALUES (" .
                join(",", array_map(fn($key) => ":$key", array_keys($data))) .
                ")"
        );
        $statement->execute($data);
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
        $statement = $this->getPDO()->prepare(
            "DELETE FROM " .
                $this->getTableName() .
                " WHERE " .
                $this->getPrimaryKey() .
                " = ? LIMIT 1"
        );
        $statement->execute([$id]);
    }

    public function assertDatabaseRowExists($id, $message = "")
    {
        $statement = $this->getPDO()->prepare(
            "SELECT * FROM " .
                $this->getTableName() .
                " WHERE " .
                $this->getPrimaryKey() .
                " = ? LIMIT 1"
        );
        $statement->execute([$id]);
        $this->assertNotFalse($statement->fetch(), $message);
    }

    public function asssertDatabaseRowDoesNotExist($id, $message = "")
    {
        $statement = $this->getPDO()->prepare(
            "SELECT * FROM " .
                $this->getTableName() .
                " WHERE " .
                $this->getPrimaryKey() .
                " = ? LIMIT 1"
        );
        $statement->execute([$id]);
        $this->assertFalse($statement->fetch(), $message);
    }

    public function assertDatabaseRowMatch(array $data, $message = "")
    {
        $statement = $this->getPDO()->prepare(
            "SELECT * FROM " .
                $this->getTableName() .
                " WHERE " .
                join(
                    " AND ",
                    array_map(fn($key) => "$key = :$key", array_keys($data))
                ) .
                " LIMIT 1"
        );
        $statement->execute($data);
        $this->assertNotFalse($statement->fetch(), $message);
    }

    public function assertDatabaseRowDoesNotMatch(array $data, $message = "")
    {
        $statement = $this->getPDO()->prepare(
            "SELECT * FROM " .
                $this->getTableName() .
                " WHERE " .
                join(
                    " AND ",
                    array_map(fn($key) => "$key = :$key", array_keys($data))
                ) .
                " LIMIT 1"
        );
        $statement->execute($data);
        $this->assertFalse($statement->fetch(), $message);
    }

    public function assertDatabaseExactRowExists(array $data, $message = "")
    {
        $statement = $this->getPDO()->prepare(
            "SELECT * FROM " .
                $this->getTableName() .
                " WHERE " .
                join(
                    " AND ",
                    array_map(fn($key) => "$key = :$key", array_keys($data))
                ) .
                " LIMIT 1"
        );
        $statement->execute($data);
        $this->assertNotFalse(
            $row = $statement->fetch(PDO::FETCH_ASSOC),
            $message
        );

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
        $statement = $this->getPDO()->prepare(
            "SELECT * FROM " .
                $this->getTableName() .
                " WHERE " .
                join(
                    " AND ",
                    array_map(fn($key) => "$key = :$key", array_keys($data))
                ) .
                " LIMIT 1"
        );
        $statement->execute($data);
        $row = $statement->fetch();
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
        $statement = $this->getPDO()->prepare(
            "SELECT * FROM " . $this->getTableName()
        );
        $statement->execute();
        $table = $statement->fetchAll(PDO::FETCH_ASSOC);
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

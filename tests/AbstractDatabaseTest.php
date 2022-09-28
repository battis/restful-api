<?php

namespace Tests\Battis\CRUD;

use Envms\FluentPDO\Query;
use PDO;
use PHPUnit\Framework\TestCase;

abstract class AbstractDatabaseTest extends TestCase
{
    /** @var PDO */
    private static $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO(self::getDSN());
    }

    protected static function getDSN(): string
    {
        return "sqlite::memory:";
    }

    protected function setUp(): void
    {
        self::$pdo->query($this->getSetupSQL());
    }

    abstract protected function getSetupSQL(): string;

    protected function tearDown(): void
    {
        self::$pdo->query($this->getTearDownSQL());
    }

    abstract protected function getTearDownSQL(): string;

    protected function query(): Query
    {
        return new Query(self::$pdo);
    }

    abstract protected function getTableName(): string;

    public function assertDatabaseRowExists($id)
    {
        $this->assertNotFalse(
            $this->query()
                ->from($this->getTableName(), $id)
                ->fetch()
        );
    }

    public function asssertDatabaseRowDoesNotExist($id)
    {
        $this->assertFalse(
            $this->query()
                ->from($this->getTableName(), $id)
                ->fetch()
        );
    }

    public function assertDatabaseRowMatch(array $data)
    {
        $query = $this->query()->from($this->getTableName());
        foreach ($data as $key => $value) {
            $query = $query->where("`$key` = ?", $value);
        }
        $this->assertNotFalse($query->fetch());
    }

    public function assertDatabaseRowDoesNotMatch(array $data)
    {
        $query = $this->query()->from($this->getTableName());
        foreach ($data as $key => $value) {
            $query = $query->where("`$key` = ?", $value);
        }
        $this->assertFalse($query->fetch());
    }

    public function assertDatabaseExactRowExists(array $data)
    {
        $query = $this->query()->from($this->getTableName());
        foreach ($data as $key => $value) {
            $query = $query->where("`$key` = ?", $value);
        }
        $this->assertNotFalse($row = $query->fetch());

        foreach ($row as $key => $value) {
            $this->assertEquals($data[$key], $value);
        }
        foreach ($data as $key => $value) {
            $this->assertEquals($row[$key], $value);
        }
    }

    public function assertDatabaseExactRowDoesNotExist(array $data)
    {
        $query = $this->query()->from($this->getTableName());
        foreach ($data as $key => $value) {
            $query = $query->where("`$key` = ?", $value);
        }
        $row = $query->fetch();
        if ($row == false) {
            $this->assertFalse($row);
        } else {
            $this->assertFalse(
                array_reduce(
                    $row,
                    function ($state, $value) use ($data) {
                        return $state && in_array($value, array_keys($data));
                    },
                    true
                )
            );
        }
    }

    public function assertDatabaseTableEquals(array $data)
    {
        $table = $this->query()
            ->from($this->getTableName())
            ->fetchAll();
        $this->assertEquals(count($data), count($table));
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
            $this->assertTrue($matched);
        }
    }
}

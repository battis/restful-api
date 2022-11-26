<?php

namespace Battis\CRUD\Tests;

use PDO;
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\DataSet\CsvDataSet;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PHPUnit\DbUnit\TestCaseTrait;

abstract class TestCase extends PHPUnitTestCase {
    use TestCaseTrait;

    static private ?PDO $pdo = null;

    private ?DefaultConnection $connection = null;

    static private string $fixturePath = __DIR__ . '/Fixtures';

    final public function getPDO(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO('sqlite::memory:');
        }
        return self::$pdo;
    }

    final public function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->createDefaultDBConnection($this->getPDO());
        }
        return $this->connection;
    }

    final protected function getFixturePath(string $filePath): string
    {
        return self::$fixturePath . preg_replace('@^' . __DIR__ . '@', '', dirname($filePath)) . '/' . basename($filePath, '.php');
    }

    final protected function getCsvDataSet($filePath, $tableName, $datasetName = null): CsvDataSet
    {
        $datasetName = $datasetName ?? $tableName;
        $dataset = new CsvDataSet();
        $dataset->addTable($tableName, $this->getFixturePath($filePath) . '/' . basename($datasetName, '.csv') . '.csv');
        return $dataset;
    }

    final protected function assertTableEqualsCsv(string $tableName, string $filePath, string $datasetName) {
        $this->assertTablesEqual(
            $this->getCsvDataSet($filePath, $tableName, $datasetName)->getTable($tableName),
            $this->getConnection()->createQueryTable($tableName, "SELECT * FROM `$tableName`")
        );

    }
}

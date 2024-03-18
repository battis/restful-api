<?php

namespace Battis\CRUD\Tests;

use Battis\DataUtilities\PHPUnit\FixturePath;
use PDO;
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\DataSet\CsvDataSet;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PHPUnit\DbUnit\TestCaseTrait;

abstract class TestCase extends PHPUnitTestCase {
    use TestCaseTrait, FixturePath;

    static private ?PDO $pdo = null;

    private ?DefaultConnection $connection = null;

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

    final protected function getCsvDataSet($tableName, $datasetName = null): CsvDataSet
    {
        $datasetName = $datasetName ?? $tableName;
        $dataset = new CsvDataSet();
        $dataset->addTable($tableName, $this->getPathToFixture(basename($datasetName, '.csv')) . '.csv');
        return $dataset;
    }

    final protected function assertTableEqualsCsv(string $tableName, string $datasetName) {
        $this->assertTablesEqual(
            $this->getCsvDataSet($tableName, $datasetName)->getTable($tableName),
            $this->getConnection()->createQueryTable($tableName, "SELECT * FROM `$tableName`")
        );

    }
}

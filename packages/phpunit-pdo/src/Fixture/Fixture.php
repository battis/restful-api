<?php

namespace Battis\PHPUnit\PDO\Fixture;

use Battis\PHPUnit\PDO\Exceptions\SchemaException;
use Battis\PHPUnit\PDO\Query;
use PDO;

/**
 * @extends Schema<string, Table, Table>
 */
class Fixture extends Schema
{
    /** @var array<string, Table> */
    private array $tables = [];

    public const TABLES = 'tables';

    /**
     * @param array{tables: array<string, array{name: string, rows: array<int, array<string, mixed>>}>} $config
     */
    protected function __construct(array $config)
    {
        $this->isIterableAs($this->tables);
        foreach ($config[self::TABLES] as $data) {
            $this->addTable(Table::fromArray($data)->inFixture($this));
        }
    }

    /**
     * @param array{tables: array<string, array{name: string, rows: array<int, array<string, mixed>>}>} $array
     */
    public static function fromArray(array $array): Fixture
    {
        return new Fixture($array);
    }

    public function createIn(PDO $pdo)
    {
        if (!$this->getSchema()) {
            throw new SchemaException('Schema not defined');
        }
        // TODO is there a more robust way to handle multiple SQL queries?
        foreach (explode(';', $this->getSchema()->getSQL()) as $sql) {
            Query::fromString($sql)->executeWith($pdo);
        }
    }

    public function setUp(PDO $pdo): void
    {
        $this->createIn($pdo);
        foreach ($this->tables as $table) {
            $table->setUp($pdo);
        }
    }

    public function tearDown(PDO $pdo): void
    {
        foreach ($this->tables as $table) {
            $table->tearDown($pdo);
        }
    }

    public function equals(Base $other): bool
    {
        if ($other instanceof Fixture) {
            foreach ($this->tables as $name => $table) {
                if (!$table->equals($other->tables[$name])) {
                    return false;
                }
            }
            return count($this->tables) == count($other->tables);
        }
        return false;
    }

    private function addTable(Table $table): void
    {
        $this->tables[$table->getName()] = $table;
    }

    /**
     * @return string[]
     */
    public function getTableNames(): array
    {
        return array_keys($this->tables);
    }

    /**
     * @return array<string, Table>
     */
    public function getTables(): array
    {
        return $this->tables;
    }
}

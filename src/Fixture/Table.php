<?php

namespace Battis\PHPUnit\PDO\Fixture;

use Battis\PHPUnit\PDO\Query;
use PDO;

/**
 * @extends Schema<int, Row, Row>
 */
class Table extends Schema
{
    private string $name;

    /** @var Row[] */
    private array $rows = [];

    private ?Fixture $parent = null;

    public const NAME = 'name';
    public const ROWS = 'rows';

    /**
     * @param array{name: string, rows: array<int, array<string, mixed>>} $config
     */
    protected function __construct(array $config)
    {
        $this->isIterableAs($this->rows);
        $this->name = $config[self::NAME];
        foreach ($config[self::ROWS] as $data) {
            $this->addRow(Row::fromArray($data)->inTable($this));
        }
    }

    /**
     * @param array{name: string, rows: array<int, array<string, mixed>>} $array
     */
    public static function fromArray(array $array): Table
    {
        return new Table($array);
    }

    public function inFixture(Fixture $parent): Table
    {
        $this->parent = $parent;
        return $this;
    }

    public function setUp(PDO $pdo): void
    {
        if (!$this->parent) {
            $this->createIn($pdo);
        }
        Query::truncate($this)->executeWith($pdo);
        $this->insertAll($pdo);
    }

    public function tearDown(PDO $pdo): void
    {
        Query::drop($this)->executeWith($pdo);
    }

    public function insertAll(PDO $pdo): void
    {
        foreach ($this->rows as $row) {
            $row->insertInto($pdo);
        }
    }

    public function equals(Base $other): bool
    {
        if ($other instanceof Table) {
            if ($this->name === $other->name) {
                foreach ($this->rows as $name => $row) {
                    if (!$row->equals($other->rows[$name])) {
                        return false;
                    }
                }
                return count($this->rows) === count($other->rows);
            }
        }
        return false;
    }

    private function addRow(Row $row): void
    {
        array_push($this->rows, $row);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getColumnNames()
    {
        if (count($this->rows)) {
            return $this->rows[0]->getColumnNames();
        } else {
            return [];
        }
    }

    /**
     * @return Row[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}

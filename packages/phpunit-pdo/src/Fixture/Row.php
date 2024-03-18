<?php

namespace Battis\PHPUnit\PDO\Fixture;

use Battis\PHPUnit\PDO\Exceptions\RowException;
use Battis\PHPUnit\PDO\Query;
use PDO;

/**
 * @extends Base<string, Column, string>
 */
class Row extends Base
{
    /** @var array<string, Column> */
    private $columns = [];

    private ?Table $parent = null;

    /**
     * @param array<string, mixed> $config
     */
    protected function __construct(array $config)
    {
        $this->isIterableAs($this->columns);
        foreach ($config as $name => $value) {
            $this->addColumn(new Column($name, $value));
        }
    }

    /**
     * @param string $offset
     * @param Column $value
     * @return mixed
     */
    protected function hookGetValue(mixed $offset, mixed $value): mixed
    {
        return $value->getValue();
    }

    /**
     * @param array<string, mixed> $array
     */
    public static function fromArray(array $array): Row
    {
        return new Row($array);
    }

    public function inTable(Table $parent): Row
    {
        $this->parent = $parent;
        return $this;
    }

    public function insertInto(PDO $pdo): void
    {
        if (!$this->parent) {
            throw new RowException('No parent registered');
        }
        Query::insertInto($this->parent)->executeWith($pdo, $this->getValues());
    }

    public function equals(Base $other): bool
    {
        if ($other instanceof Row) {
            foreach ($other->columns as $name => $column) {
                if (!$column->equals($this->columns[$name])) {
                    return false;
                }
            }
            return count($this->columns) === count($other->columns);
        }
        return false;
    }

    private function addColumn(Column $column): void
    {
        $this->columns[$column->getName()] = $column;
    }

    /**
     * @return string[]
     */
    public function getColumnNames(): array
    {
        return array_values(
            array_map(fn(Column $column) => $column->getName(), $this->columns)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return array_map(
            fn(Column $column) => $column->getValue(),
            $this->columns
        );
    }
}

<?php

namespace Battis\PHPUnit\PDO\Constraints;

use Battis\PHPUnit\PDO\Fixture\Row;
use Battis\PHPUnit\PDO\Fixture\Table;
use Battis\PHPUnit\PDO\Query;
use PDO;
use PHPUnit\Framework\Constraint\Constraint;

class TableContainsRow extends Constraint
{
    private Table $table;
    private PDO $pdo;

    public function __construct(Table $table, PDO $pdo)
    {
        $this->table = $table;
        $this->pdo = $pdo;
    }

    /*
     * @param Row $expected
     * @return bool
     */
    public function matches(mixed $expected): bool
    {
        if ($expected instanceof Row) {
            $response = Query::selectRowFrom($this->table)->fetchFrom(
                $this->pdo,
                $expected->getValues()
            );
            if (is_array($response)) {
                $actual = Row::fromArray($response);
                return $actual->equals($expected);
            }
        }
        return false;
    }

    public function toString(): string
    {
        return "row is contained in table '{$this->table->getName()}' in database";
    }
}

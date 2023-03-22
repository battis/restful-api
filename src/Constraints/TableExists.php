<?php

namespace Battis\PHPUnit\PDO\Constraints;

use Battis\PHPUnit\PDO\Fixture\Table;
use Battis\PHPUnit\PDO\Query;
use Exception;
use PDO;
use PHPUnit\Framework\Constraint\Constraint;

class TableExists extends Constraint
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param Table $table
     * @return bool
     */
    public function matches(mixed $table): bool
    {
        /*
         * TODO find a consistently fast way of checking if table exists
         *   [Lots of discussion here](https://stackoverflow.com/questions/1717495)
         *   The core problem is that every database server has its own
         *   query to specifically check if a table exists, so a server-
         *   agnostic query is tricky to work out.
         */
        try {
            $result = Query::fromString(
                "SELECT COUNT(*) FROM `{$table->getName()}`"
            )->fetchFrom($this->pdo);
        } catch (Exception $e) {
            return false;
        }
        return $result !== false;
    }

    public function toString(): string
    {
        return 'table exists in database';
    }
}

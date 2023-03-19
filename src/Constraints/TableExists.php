<?php

namespace Battis\PHPUnit\PDO\Constraints;

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
    public function matches($table): bool
    {
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

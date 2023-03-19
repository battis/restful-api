<?php

namespace Battis\PHPUnit\PDO;

use Battis\PHPUnit\PDO\Fixture\Table;
use PDO;
use PDOStatement;

class Query
{
    private string $sql;
    private ?PDOStatement $statement = null;

    private function __construct(string $sql)
    {
        $this->sql = $sql;
    }

    public static function fromString(string $sqlString): Query
    {
        return new Query($sqlString);
    }

    public static function fromSqlFile(string $pathToFile): Query
    {
        return self::fromString(file_get_contents($pathToFile));
    }

    public static function insertInto(Table $table): Query
    {
        return new Query(
            "INSERT INTO `{$table->getName()}` (`" .
                join('`, `', $table->getColumnNames()) .
                '`) VALUES (:' .
                join(', :', $table->getColumnNames()) .
                ')'
        );
    }

    public static function selectAllFrom(Table $table): Query
    {
        return new Query("SELECT * FROM `{$table->getName()}`");
    }

    public static function selectRowFrom(Table $table): Query
    {
        return new Query(
            "SELECT * FROM `{$table->getName()}` WHERE " .
                join(
                    ' AND ',
                    array_map(
                        fn($name) => "`$name` = :$name",
                        $table->getColumnNames()
                    )
                )
        );
    }

    public static function updateIn(
        Table $table,
        string $primaryKey = 'id'
    ): Query {
        return new Query(
            "UPDATE `{$table->getName()}` SET " .
                join(
                    ', ',
                    array_map(
                        fn($name) => "`$name` = :$name",
                        $table->getColumnNames()
                    )
                ) .
                " WHERE `$primaryKey` = :$primaryKey"
        );
    }

    public static function deleteFrom(
        Table $table,
        string $primaryKey = 'id'
    ): Query {
        return new Query(
            "DELETE FROM `{$table->getName()}` WHERE `$primaryKey` = :$primaryKey"
        );
    }

    public static function truncate(Table $table): Query
    {
        return new Query("DELETE FROM `{$table->getName()}`"); // sqlite-friendly dialect
    }

    public static function drop(Table $table): Query
    {
        return new Query("DROP TABLE `{$table->getName()}`");
    }

    public function getSQL(): string
    {
        return $this->sql;
    }

    public function prepare(PDO $pdo): Query
    {
        $this->statement = $pdo->prepare($this->sql);
        return $this;
    }

    public function execute(?array $params = null): Query
    {
        $this->statement->execute($params);
        return $this;
    }

    /**
     * @return array<string, mixed>|false
     */
    public function fetch(int $mode = PDO::FETCH_ASSOC): mixed
    {
        return $this->statement->fetch($mode);
    }

    /**
     * @return mixed[][]
     */
    public function fetchAll(int $mode = PDO::FETCH_ASSOC): array
    {
        return $this->statement->fetchAll($mode);
    }

    public function executeWith(PDO $pdo, ?array $params = null)
    {
        return $this->prepare($pdo)->execute($params);
    }

    public function fetchFrom(
        PDO $pdo,
        ?array $params = null,
        int $mode = PDO::FETCH_ASSOC
    ) {
        return $this->prepare($pdo)
            ->execute($params)
            ->fetch($mode);
    }

    public function fetchAllFrom(
        PDO $pdo,
        ?array $params = null,
        int $mode = PDO::FETCH_ASSOC
    ): array {
        return $this->prepare($pdo)
            ->execute($params)
            ->fetchAll($mode);
    }
}

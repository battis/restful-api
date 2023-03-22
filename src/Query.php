<?php

namespace Battis\PHPUnit\PDO;

use Battis\PHPUnit\PDO\Exceptions\QueryException;
use Battis\PHPUnit\PDO\Fixture\Table;
use PDO;
use PDOStatement;

class Query
{
    /*
     * FIXME if `$sql` contains multiple valid queries, only the first will be run
     *   cf [this whinge-fist](https://stackoverflow.com/questions/6346674)
     */
    private string $sql;
    private ?PDOStatement $statement = null;

    private function __construct(string $sql)
    {
        $this->sql = $sql;
    }

    public function getStatement(): PDOStatement {
        if ($this->statement) {
            return $this->statement;
        } else {
            throw new QueryException('statement not prepared');
        }
    }

    public static function fromString(string $sqlString): Query
    {
        return new Query($sqlString);
    }

    public static function fromSqlFile(string $pathToFile): Query
    {
        $sql = file_get_contents($pathToFile);
        if ($sql) {
            return self::fromString($sql);
        } else {
            throw new QueryException("'$pathToFile' invalid");
        }
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

    /**
     * @param array<string, mixed>|mixed[]|null $params
     * @return Query
     */
    public function execute(?array $params = null): Query
    {
        $this->getStatement()->execute($params);
        return $this;
    }

    /**
     * @return mixed
     */
    public function fetch(int $mode = PDO::FETCH_ASSOC): mixed
    {
        return $this->getStatement()->fetch($mode);
    }

    /**
     * @return mixed[][]
     */
    public function fetchAll(int $mode = PDO::FETCH_ASSOC): array
    {
        return $this->getStatement()->fetchAll($mode);
    }

    /**
     * @param PDO $pdo
     * @param array<string, mixed>|mixed[]|null $params
     * @return Query
     */
    public function executeWith(PDO $pdo, ?array $params = null): Query
    {
        return $this->prepare($pdo)->execute($params);
    }

    /**
     * @param PDO $pdo
     * @param array<string, mixed>|mixed[]|null $params
     * @param int $mode
     * @return mixed
     */
    public function fetchFrom(
        PDO $pdo,
        ?array $params = null,
        int $mode = PDO::FETCH_ASSOC
    ): mixed {
        return $this->prepare($pdo)
            ->execute($params)
            ->fetch($mode);
    }

    /**
     * @param PDO $pdo
     * @param array<string, mixed>|mixed[]|null $params
     * @param int $mode
     * @return mixed[][]
     */
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

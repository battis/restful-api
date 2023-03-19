<?php

namespace Battis\PHPUnit\PDO\Tests\Database;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Traits\PDO as PDOTrait;
use PDO;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    use FixturePath, PDOTrait;

    public function testQuery(): void
    {
        $sequence = [
            'create.sql',
            'select.sql',
            'insert.sql',
            'select.sql',
            'update.sql',
            'select.sql',
            'delete.sql',
            'select.sql',
            'truncate.sql',
            'select.sql',
            'drop.sql',
        ];
        $actual = [];
        $expected = [];

        foreach ($sequence as $fixture) {
            $path = $this->getPathToFixture($fixture);
            $query = Query::fromSqlFile($path);
            $this->assertEquals(file_get_contents($path), $query->getSQL());
            array_push($actual, $query->fetchAllFrom($this->getPDO()));
        }
        self::destroyPDO();

        foreach ($sequence as $fixture) {
            $statement = $this->getPDO()->prepare(
                file_get_contents($this->getPathToFixture($fixture))
            );
            $statement->execute();
            array_push($expected, $statement->fetchAll(PDO::FETCH_ASSOC));
        }
        self::destroyPDO();

        $this->assertEquals($expected, $actual);
    }
}

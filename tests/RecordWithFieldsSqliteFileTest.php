<?php

namespace Tests\Battis\CRUD;

use PDO;
use ReflectionClass;
use SQLite3;

class RecordWithFieldsSqliteFileTest extends RecordWithFieldsSqliteMemoryTest
{
    private static $sqlitePath = null;

    public static function setUpBeforeClass(): void
    {
        if (file_exists(self::getSqlitePath())) {
            rename(
                self::getSqlitePath(),
                dirname(self::getSqlitePath()) .
                    "/" .
                    basename(self::getSqlitePath(), ".sqlite") .
                    "-" .
                    time() .
                    ".sqlite"
            );
            new SQLite3(self::getSqlitePath());
        }
    }

    public static function tearDownAfterClass(): void
    {
        static::$sqlitePath = "";
    }

    private static function getSqlitePath(): string
    {
        if (empty(static::$sqlitePath)) {
            $reflection = new ReflectionClass(static::class);
            static::$sqlitePath =
                __DIR__ . "/" . $reflection->getShortName() . ".sqlite";
        }
        return static::$sqlitePath;
    }

    protected function getPDO(): PDO
    {
        return new PDO("sqlite:" . self::getSqlitePath());
    }
}

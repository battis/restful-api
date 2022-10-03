<?php

namespace Tests\Battis\CRUD;

use PDO;

class RecordWithFieldsMySQLTest extends RecordWithFieldsSqliteMemoryTest
{
    protected function getPDO(): PDO
    {
        return new PDO(
            "mysql:host=127.0.0.1;port=8889;dbname=phpunit",
            "phpunit",
            "tN)knuF72Q*AFvS1"
        );
    }
}

<?php

namespace Battis\PHPUnit\PDO\Tests\Fixtures\Traits\PDOTest;

use Battis\PHPUnit\PDO\Traits\PDO as PDOTrait;
use PDO;

class PDOFixture
{
    use PDOTrait;

    public static function fixtureGetPDO(): ?PDO
    {
        return static::$pdo;
    }
}

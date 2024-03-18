<?php

namespace Battis\PHPUnit\PDO\Traits;

use PDO as GlobalPDO;

trait PDO
{
    private static ?GlobalPDO $pdo = null;

    public static function getPDO(): GlobalPDO
    {
        if (!self::$pdo) {
            self::$pdo = static::createPDO();
            self::$pdo->setAttribute(GlobalPDO::ATTR_EMULATE_PREPARES, 1);
        }
        return self::$pdo;
    }

    public static function createPDO(): GlobalPDO
    {
        return new GlobalPDO('sqlite::memory:');
    }

    public static function destroyPDO(): void
    {
        self::$pdo = null;
    }
}

<?php

namespace Battis\PHPUnit\PDO\Traits;

use PDO as GlobalPDO;

trait PDO
{
    private static ?GlobalPDO $pdo = null;

    public static function getPDO(): GlobalPDO
    {
        if (!static::$pdo) {
            static::$pdo = static::createPDO();
            static::$pdo->setAttribute(GlobalPDO::ATTR_EMULATE_PREPARES, 1);
        }
        return static::$pdo;
    }

    public static function createPDO(): GlobalPDO
    {
        return new GlobalPDO('sqlite::memory:');
    }

    public static function destroyPDO(): void
    {
        static::$pdo = null;
    }
}

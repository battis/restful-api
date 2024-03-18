<?php

namespace Battis\CRUD;

use Envms\FluentPDO\Query;
use PDO;

class Connection
{
    /** @var self */
    private static $instance;

    /** @var PDO */
    private $pdo;

    public static function setPDO(PDO $pdo)
    {
        self::getInstance($pdo);
    }

    public static function getInstance(PDO $pdo = null): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    private function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    public static function createQuery(): Query
    {
        return new Query(self::getInstance()->getPDO());
    }
}

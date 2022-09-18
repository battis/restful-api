<?php

namespace Battis\CRUD;

use Doctrine\DBAL;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;

class Manager
{
    /** @var self */
    private static $instance;

    /** @var DBAL\Connection */
    private $connection;

    public static function get(DBAL\Connection $connection = null): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self($connection);
        }
        return self::$instance;
    }

    private function __construct(DBAL\Connection $connection)
    {
        assert(
            $connection !== null,
            new Exception("Cannot create Manager without DBAL connection")
        );
        $this->connection = $connection;
    }

    public function connection(): DBAL\Connection
    {
        return $this->connection;
    }

    public function queryBuilder(): QueryBuilder
    {
        return $this->connection->createQueryBuilder();
    }
}

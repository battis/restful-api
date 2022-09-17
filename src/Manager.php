<?php

namespace Battis\CRUD;

use Battis\CRUD\Exceptions\DatabaseException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;

class Manager
{
    /** @var self */
    private static $instance;

    /** @var Connection */
    private $dbal;

    public static function get(Connection $dbal = null): self
    {
        if (empty(self::$instance)) {
            if (empty($dbal)) {
                throw new Exception(
                    "Cannot create Manager without DBAL connection"
                );
            }
            self::$instance = new self($dbal);
        }
        return self::$instance;
    }

    public static function setConnection(Connection $dbal)
    {
        new self($dbal);
    }

    private function __construct(Connection $dbal)
    {
        assert($dbal !== null, new DatabaseException());
        $this->dbal = $dbal;
    }

    public function connection(): Connection
    {
        return $this->dbal;
    }

    public function queryBuilder(): QueryBuilder
    {
        return $this->dbal->createQueryBuilder();
    }

    public function deferStatement(QueryBuilder $statement)
    {
        array_push($this->delayedStatements, $statement);
    }
}

<?php

namespace Battis\OAuth2\Server\Repositories\Traits;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

trait DBAL
{
    /** @var Connection */
    private $connection;

    /**  @var QueryBuilder */
    private $dbal_queryBuilder;

    protected function queryBuilder()
    {
        if (empty($this->dbal_queryBuilder)) {
            $this->dbal_queryBuilder = $this->connection->createQueryBuilder();
        }
        return $this->dbal_queryBuilder;
    }

    protected function q()
    {
        return $this->queryBuilder();
    }
}

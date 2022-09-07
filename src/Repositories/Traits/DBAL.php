<?php

namespace Battis\OAuth2\Server\Repositories\Traits;

use DI\Annotation\Inject;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

trait DBAL
{
    /**
     * @Inject
     * @var Connection
     */
    private $connection;

    /**
     * @var QueryBuilder
     */
    private $_queryBuilder;

    protected function queryBuilder()
    {
        if (empty($this->queryBuilder)) {
            $this->_queryBuilder = $this->connection->createQueryBuilder();
        }
        return $this->_queryBuilder;
    }

    protected function q()
    {
        return $this->queryBuilder();
    }
}

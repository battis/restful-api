<?php

namespace Battis\OAuth2\Server\Repositories\Traits;

use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Doctrine\DBAL\Driver\IBMDB2\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

trait TokenRepositoryTrait
{
    use DBAL;

    protected $table;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function persistToken($parameters)
    {
        try {
            $this->queryBuilder()
                ->insert($this->table)
                ->values(
                    array_combine(
                        array_keys($parameters),
                        array_map(fn($key) => ":$key", array_keys($parameters))
                    )
                )
                ->setParameters($parameters)
                ->executeStatement();
        } catch (UniqueConstraintViolationException $e) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    public function revokeToken($tokenId)
    {
        $this->queryBuilder()
            ->delete($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("identifier", ":i")
            )
            ->setParameter("i", $tokenId)
            ->executeStatement();
    }

    public function isTokenRevoked($tokenId)
    {
        return $this->queryBuilder()
            ->select("*")
            ->from($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("identifier", ":i")
            )
            ->setParameter("i", $tokenId)
            ->executeQuery()
            ->rowCount() == 0;
    }
}

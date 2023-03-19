<?php

namespace Battis\PHPUnit\PDO\Fixture;

use Battis\PHPUnit\PDO\Exceptions\SchemaException;
use Battis\PHPUnit\PDO\Query;
use PDO;

/**
 * @template TypeKey
 * @template TypeStored
 * @template TypeAccessed
 * @extends Base<TypeKey, TypeStored, TypeAccessed>
 * @method static Schema fromArray(array)
 */
abstract class Schema extends Base
{
    private ?Query $schema = null;

    public function withSchema(Query $schema): Schema
    {
        // TODO test that `$schema` query contains `CREATE TABLE`
        // TODO insert `IF NOT EXISTS` into `CREATE TABLE` `$schema` query
        $this->schema = $schema;
        return $this;
    }

    public function getSchema(): ?Query
    {
        return $this->schema;
    }

    /**
     * @param PDO $pdo
     * @return void
     * @throws SchemaException if schema not defined
     */
    public function createIn(PDO $pdo)
    {
        if (!$this->schema) {
            throw new SchemaException('Schema not defined');
        }
        $this->schema->executeWith($pdo);
    }

    abstract public function setUp(PDO $pdo): void;

    abstract public function tearDown(PDO $pdo): void;
}

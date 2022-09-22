<?php

namespace Battis\CRUD;

use Doctrine\DBAL\Types\Type;
use Exception;

class Record
{
    /** @var Spec|null */
    protected static $spec;

    /**
     * Construct a new instance of the object from an associative array of data properties.
     *
     * @param array $data Associative array of properties (keys that do not exactly match a declared property will be ignored)
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->assign($data);
        }
    }

    protected static function getSpec(): Spec
    {
        // TODO: sort out late static binding and static properties and why this isn't working
        //if (empty(static::$spec)) {
        static::$spec = static::defineSpec();
        //}
        return static::$spec;
    }

    protected static function defineSpec(): Spec
    {
        return new Spec(static::class);
    }

    /**
     * Insert a new record into the database and return on an instance of this object representing that record, if successfully inserted.
     *
     * @param array $data Associative array of properties (keys that do not exactly match a declared property will be ignored)
     *
     * @return static|null
     */
    public static function create(array $data)
    {
        $s = static::getSpec();
        $data = $s->mapPropertiesToFields($data);
        $dbal = Manager::get();
        if (
            $dbal
                ->queryBuilder()
                ->insert($s->getTableName())
                ->values($s->getNamedParameters($data))
                ->setParameters(
                    $data,
                    array_combine(
                        array_keys($data),
                        array_map(
                            fn($value) => Type::getType(gettype($value)),
                            array_values($data)
                        )
                    )
                )
                ->executeStatement() == 1
        ) {
            return static::read($dbal->connection()->lastInsertId());
        }
        return null;
    }

    /**
     * Read a record from the database and return an instance of this object representing that record, if present
     *
     * @param mixed $id Primary key value
     *
     * @return static|null
     */
    public static function read($id)
    {
        $s = static::getSpec();
        $q = Manager::get()->queryBuilder();
        $response = $q
            ->select("*")
            ->from($s->getTableName())
            ->where($q->expr()->eq($s->getPrimaryKeyFieldName(), "?"))
            ->setParameter(0, $id)
            ->executeQuery();
        if ($response->rowCount() === 1) {
            return new static($response->fetchAssociative());
        }
        return null;
    }

    /**
     * Retrieve all records matching an optional array of properties as an array of objects representing those records
     *
     * @param array $data Optional associative array of properties to match (keys that do not exactly match a declared property will be ignored)
     *
     * @return static[]
     */
    public static function retrieve(array $data = []): array
    {
        $q = Manager::get()->queryBuilder();
        $s = static::getSpec();
        $q->select("*")->from($s->getTableName());

        if (!empty($data)) {
            $data = $s->mapPropertiesToFields($data);
            $q = $q
                ->where(
                    join(
                        " AND ",
                        array_map(fn($key) => "$key = :$key", array_keys($data))
                    )
                )
                ->setParameters($data);
        }
        $response = $q->executeQuery();

        $result = [];
        while ($row = $response->fetchAssociative()) {
            array_push($result, new static($row));
        }
        return $result;
    }

    /**
     * Update a record in the database and return an instance of this object representing that record, if successful
     *
     * @param array $data Associative array of properties (keys that do not exactly match a declared property will be ignored). *Must* include a primary key property to identify the record to be updated.
     *
     * @return static|null
     */
    public static function update(array $data)
    {
        $s = static::getSpec();
        $data = $s->mapPropertiesToFields($data);
        if (key_exists($s->getPrimaryKeyFieldName(), $data)) {
            $result = self::read($data[$s->getPrimaryKeyFieldName()]);
            $result->save($data);
            return $result;
        }
        return null;
    }

    /**
     * Delete a record from the database, returning an object representing the deleted record, if successful
     *
     * @param mixed $id Primary key value
     *
     * @return static|null
     */
    public static function delete($id)
    {
        $s = static::getSpec();
        $result = static::read($id);
        if ($result) {
            $q = Manager::get()->queryBuilder();
            if (
                $q
                    ->delete($s->getTableName())
                    ->where($q->expr()->eq($s->getPrimaryKeyFieldName(), "?"))
                    ->setParameter(0, $id)
                    ->executeStatement() > 0
            ) {
                return $result;
            }
        }
        return null;
    }

    private function assign(array $data)
    {
        $s = static::getSpec();
        $data = $s->mapFieldsToProperties($data);
        foreach ($data as $property => $value) {
            if ($setter = $s->getSetter($property)) {
                $this->$setter($value);
            } else {
                $this->$property = $value;
            }
        }
    }

    private function cloneIntoSelf(self $other)
    {
        $this->assign((array) $other);
    }

    private function getPrimaryKeyValue()
    {
        $property = static::getSpec()->getPrimaryKeyPropertyName();
        return $this->$property;
    }

    public function save(array $data = []): void
    {
        $s = static::getSpec();
        $data = $s->mapPropertiesToFields(
            array_merge((array) $this, $s->mapFieldsToProperties($data))
        );
        $q = Manager::get()->queryBuilder();
        $q->update($s->getTableName())
            ->values(
                $s->getNamedParameters($data),
                array_map(
                    fn($value) => Type::getType(gettype($value)),
                    array_values($data)
                )
            )
            ->setParameters($data)
            ->executeStatement();
        $updated = static::read($this->getPrimaryKeyValue());
        if ($updated) {
            $this->cloneIntoSelf($updated);
        } else {
            throw new Exception("Record no longer available");
        }
    }
}

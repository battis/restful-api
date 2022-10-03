<?php

namespace Battis\CRUD;

use Battis\CRUD\Exceptions\RecordException;
use Battis\CRUD\Utilities\Types;

abstract class Record
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

        $id = Connection::createQuery()
            ->insertInto($s->getTableName())
            ->values(
                Types::toDatabaseValues(static::objectToDatabaseHook($data))
            )
            ->execute();
        if ($id !== false) {
            /* FIXME: this if statement is a work-around for (I think)
             * a bug in FluentPDO where the first inserted row always
             * returns '0' as the lastInsertedId(), even when the id
             * that was inserted is NOT '0'
             */
            if ($id == "0" && key_exists($s->getPrimaryKey(), $data)) {
                $id = $data[$s->getPrimaryKey()];
            }
            return static::read($id);
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
        if (
            $data = Connection::createQuery()
                ->from($s->getTableName())
                ->where("`" . $s->getPrimaryKey() . "` = ?", $id)
                ->limit(1)
                ->fetch()
        ) {
            return new static(static::databaseToObjectHook($data));
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
        $s = static::getSpec();

        $query = Connection::createQuery()->from($s->getTableName());
        if (!empty($data)) {
            $query = $query->where($data);
        }
        $result = [];
        if ($response = $query->execute()) {
            while ($row = $response->fetch()) {
                array_push(
                    $result,
                    new static(static::databaseToObjectHook($row))
                );
            }
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
        if (key_exists($s->getPrimaryKey(), $data)) {
            $result = self::read($data[$s->getPrimaryKey()]);
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
        if (
            $result &&
            Connection::createQuery()
                ->delete($s->getTableName())
                ->where("`" . $s->getPrimaryKey() . "` = ?", $id)
        ) {
            return $result;
        }
        return null;
    }

    private function assign(array $data)
    {
        $s = static::getSpec();
        foreach ($data as $property => $value) {
            if ($setter = $s->getSetter($property)) {
                $this->$setter(
                    Types::toExpectedArgumentType($this, $setter, $value)
                );
            } else {
                $this->$property = $value;
            }
        }
    }

    private function cloneIntoSelf(self $other)
    {
        $this->assign((array) $other);
    }

    private function getPrimaryKey()
    {
        $property = static::getSpec()->getPrimaryKey();
        return $this->$property;
    }

    public function save(array $data = []): void
    {
        $s = static::getSpec();
        $result = Connection::createQuery()
            ->update($s->getTableName())
            ->set(
                Types::toDatabaseValues(
                    static::objectToDatabaseHook(
                        array_merge((array) $this, $data)
                    )
                )
            )
            ->where("`" . $s->getPrimaryKey() . "` = ?", $this->getPrimaryKey())
            ->limit(1)
            ->execute();
        if ($result) {
            $updated = static::read($this->getPrimaryKey());
            if ($updated) {
                $this->cloneIntoSelf($updated);
                unset($updated);
                return;
            }
        }
        throw new RecordException(
            "Record no longer available",
            RecordException::SAVE_ERROR
        );
    }

    protected static function objectToDatabaseHook(array $data): array
    {
        return $data;
    }

    protected static function databaseToObjectHook(array $data): array
    {
        return $data;
    }
}

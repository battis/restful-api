<?php

namespace Battis\CRUD;

use Exception;
use PDOException;
use ReflectionClass;
use ReflectionProperty;

class Record
{
    protected static $crud_tableName;
    protected static $crud_primaryKey = "id";
    protected static $crud_propertyMapping = [];

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

    /**
     * Insert a new record into the database and return on an instance of this object representing that record, if successfully inserted.
     *
     * @param array $data Associative array of properties (keys that do not exactly match a declared property will be ignored)
     *
     * @return static|null
     */
    public static function create(array $data)
    {
        $data = static::filterData($data);
        $dbal = Manager::get();
        if (
            $dbal
                ->queryBuilder()
                ->insert(static::getTableName())
                ->values(self::parameterize($data))
                ->setParameters($data)
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
        $q = Manager::get()->queryBuilder();
        $response = $q
            ->select("*")
            ->from(static::getTableName())
            ->where($q->expr()->eq(static::getPrimaryKey(), "?"))
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
        $q->select("*")->from(static::getTableName());

        if (!empty($data)) {
            $data = static::filterData($data);
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
        if (key_exists(static::getPrimaryKey(), $data)) {
            $result = self::read($data[static::getPrimaryKey()]);
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
        $result = static::read($id);
        if ($result) {
            $q = Manager::get()->queryBuilder();
            if (
                $q
                    ->delete(static::getTableName())
                    ->where($q->expr()->eq(static::getPrimaryKey(), "?"))
                    ->setParameter(0, $id)
                    ->executeStatement() > 0
            ) {
                return $result;
            }
        }
        return null;
    }

    private static function filterData($data): array
    {
        $reflector = new ReflectionClass(static::class);
        $props = array_reduce(
            $reflector->getProperties(),
            fn(array $props, ReflectionProperty $prop) => !preg_match(
                "/^crud_/",
                $prop->getName()
            )
                ? array_merge($props, [$prop->getName()])
                : $props,
            []
        );
        $data = array_filter(
            $data,
            fn($key) => in_array($key, $props),
            ARRAY_FILTER_USE_KEY
        );
        return $data;
    }

    private function assign(array $data)
    {
        $data = static::filterData($data);
        foreach ($data as $key => $value) {
            $prop = array_search($key, static::$crud_propertyMapping) ?: $key;
            $this->$prop = $value;
        }
    }

    private function cloneIntoSelf(self $other)
    {
        $this->assign((array) $other);
    }

    private static function parameterize(array $data)
    {
        return array_combine(
            array_map(
                fn($key) => static::$crud_propertyMapping[$key] ?? $key,
                array_keys($data)
            ),
            array_map(fn($key) => ":$key", array_keys($data))
        );
    }

    private static function getTableName(): string
    {
        if (empty(static::$crud_tableName)) {
            $reflection = new ReflectionClass(static::class);
            static::$crud_tableName = Helper::pluralize(
                Helper::camelCase_to_snake_case(
                    basename($reflection->getFileName(), ".php")
                )
            );
        }
        return static::$crud_tableName;
    }

    private static function getPrimaryKey(): string
    {
        return static::$crud_primaryKey;
    }

    private function getPrimaryKeyValue()
    {
        $prop = static::getPrimaryKey();
        return $this->$prop;
    }

    public function save(array $data = []): void
    {
        $data = static::filterData(array_merge((array) $this, $data));
        $q = Manager::get()->queryBuilder();
        $q->update(static::getTableName())
            ->values(self::parameterize($data))
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

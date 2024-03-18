<?php

namespace Battis\CRUD;

use Battis\DataUtilities\Text;
use ReflectionClass;
use ReflectionException;

class Spec
{
    public const DEFAULT_PRIMARY_KEY = 'id';

    /** @var ReflectionClass */
    private $reflection;

    /** @var string */
    private $tableName;

    /** @var string */
    private $primaryKey;

    public function __construct(
        string $recordType,
        string $tableName = null,
        string $primaryKey = null,
        array $options = []
    ) {
        $this->reflection = new ReflectionClass($recordType);
        assert($this->reflection->isSubclassOf(Record::class));
        $this->tableName = $this->computeTableName($tableName);
        $this->primaryKey = $this->computePrimaryKey($primaryKey);
        $this->constructorHook($options);
    }

    protected function constructorHook(array $options = [])
    {
        $options; // ah, Intelephense, if only you could suppress warnings!
    }

    protected function computeTableName(string $tableName = null): string
    {
        return empty($tableName) ?
            Text::pluralize(
                Text::camelCase_to_snake_case($this->reflection->getShortName())
            ) :
            $tableName;
    }

    protected function computePrimaryKey(string $primaryKey = null): string
    {
        return empty($primaryKey) ? static::DEFAULT_PRIMARY_KEY : $primaryKey;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getSetter(string $property, bool $isPublic = true): ?string
    {
        $setter = "set" . Text::snake_case_to_PascalCase($property);
        try {
            $method = $this->reflection->getMethod($setter);
            if (
                ($isPublic && $method->isPublic()) ||
                (!$isPublic && !$method->isPrivate())
            ) {
                return $setter;
            }
        } catch (ReflectionException $e) {
            // ignore
        }
        return null;
    }
}

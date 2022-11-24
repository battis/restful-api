<?php

namespace Battis\CRUD;

use Battis\DataUtilities\Text;
use ReflectionClass;
use ReflectionException;

class Spec
{
    public const DEFAULT_PRIMARY_KEY = "id";

    /** @var ReflectionClass */
    private $reflection;

    /** @var string */
    private $tableName;

    /** @var string */
    private $primaryKey;

    public function __construct(
        string $recordType,
        string $tableName = null,
        string $primaryKey = self::DEFAULT_PRIMARY_KEY,
        array $options = []
    ) {
        $this->reflection = new ReflectionClass($recordType);
        $this->tableName = $this->computeTableName($tableName);
        $this->primaryKey = $this->computePrimaryKey($primaryKey);
        $this->constructorHook($options);
    }

    protected function constructorHook(array $options)
    {
    }

    private function computeTableName($tableName): string
    {
        if (empty($tableName)) {
            $tableName = Text::pluralize(
                Text::camelCase_to_snake_case($this->reflection->getShortName())
            );
        }
        return $tableName;
    }

    private function computePrimaryKey(string $primaryKey): string
    {
        return $primaryKey ?: static::DEFAULT_PRIMARY_KEY;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getSetter($property)
    {
        $setter = "set" . Text::snake_case_to_PascalCase($property);
        try {
            $this->reflection->getMethod($setter);
            return $setter;
        } catch (ReflectionException $e) {
            return null;
        }
    }
}

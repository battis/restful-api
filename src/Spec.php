<?php

namespace Battis\CRUD;

use DateTime;
use DateTimeImmutable;
use Exception;
use ReflectionClass;
use ReflectionException;

class Spec
{
    public const DEFAULT_PRIMARY_KEY = "id";
    public const DEFAULT_MAPPING = [];

    /** @var ReflectionClass */
    private $reflection;

    /** @var string */
    private $tableName;

    /** @var string */
    private $primaryKeyPropertyName;

    /** @var array */
    private $propertyToFieldMapping;

    public function __construct(
        string $recordType,
        string $tableName = null,
        string $primaryKeyPropertyName = self::DEFAULT_PRIMARY_KEY,
        array $propertyToFieldMapping = self::DEFAULT_MAPPING
    ) {
        $this->reflection = new ReflectionClass($recordType);
        $this->tableName = $this->computeTableName($tableName);
        $this->primaryKeyPropertyName = $this->computePrimaryKey(
            $primaryKeyPropertyName
        );
        $this->propertyToFieldMapping = $this->computeMapping(
            $propertyToFieldMapping
        );
    }

    private function computeTableName($tableName): string
    {
        if (empty($tableName)) {
            $tableName = $this->pluralize(
                $this->camelCase_to_snake_case(
                    $this->reflection->getShortName()
                )
            );
        }
        return $tableName;
    }

    private function computePrimaryKey(string $primaryKeyPropertyName): string
    {
        return $primaryKeyPropertyName ?: static::DEFAULT_PRIMARY_KEY;
    }

    private function computeMapping($propertyToFieldMapping): array
    {
        // FIXME: needs verification
        return $propertyToFieldMapping ?? static::DEFAULT_MAPPING;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getPrimaryKeyPropertyName(): string
    {
        return $this->primaryKeyPropertyName;
    }

    public function getPrimaryKeyFieldName(): string
    {
        return $this->mapPropertyToField($this->primaryKeyPropertyName);
    }

    public function setMapping(string $property, string $field)
    {
        assert(
            !empty($property) && !empty($field),
            new Exception("Neither field nor property may be empty")
        );
        $this->propertyToFieldMapping[$property] = $field;
    }

    public function mapPropertiesToFields(array $data): array
    {
        return array_combine(
            array_map([$this, "mapPropertyToField"], array_keys($data)),
            array_values($data)
        );
    }

    public function mapFieldsToProperties(array $data): array
    {
        return array_combine(
            array_map([$this, "mapFieldToProperty"], array_keys($data)),
            array_values($data)
        );
    }

    public function mapPropertyToField(string $property): string
    {
        return $this->propertyToFieldMapping[$property] ?? $property;
    }

    public function mapFieldToProperty(string $field): string
    {
        return array_search($field, $this->propertyToFieldMapping) ?: $field;
    }

    public function getNamedParameters(array $data): array
    {
        return array_combine(
            array_keys($data),
            array_map(fn($key) => ":$key", array_keys($data))
        );
    }

    public function getSetter($property)
    {
        $setter = "set" . $this->snake_case_to_PascalCase($property);
        try {
            $this->reflection->getMethod($setter);
            return $setter;
        } catch (ReflectionException $e) {
            return null;
        }
    }

    public function toExpectedArgumentType($setter, $value)
    {
        /** @var ReflectionNamedType */
        $type = $this->reflection
            ->getMethod($setter)
            ->getParameters()[0]
            ->getType();
        if ($type) {
            $class = $type->getName();
            if ($class == "array") {
                return json_decode($value) ?? [];
            }
            return new $class($value);
        }
        return $value;
    }

    public function mapPhpTypesToDoctrineTypes(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ($value instanceof DateTimeImmutable) {
                $result[$key] = "datetime_immutable";
            } elseif ($value instanceof DateTime) {
                $result[$key] = "datetime";
            } elseif (is_array($value)) {
                $result[$key] = "json";
            }
        }
        return $result;
    }

    private function snake_case_to_PascalCase(string $snake_case): string
    {
        return join(
            array_map(
                fn($token) => strtoupper(substr($token, 0, 1)) .
                    substr($token, 1),
                explode("_", $snake_case)
            )
        );
    }

    private function camelCase_to_snake_case(string $camelCase): string
    {
        $snake_case = $camelCase;
        foreach (
            [
                "/([^0-9])([0-9])/", // separate numeric phrases
                "/([A-Z])([A-Z][a-z])/", // separate trailing word from acronym
                "/([^A-Z])([A-Z])/", // separate end of word from trailing word,
                "/([^_])_+([^_])/", // minimize underscores
            ]
            as $regexp
        ) {
            $snake_case = preg_replace($regexp, "$1_$2", $snake_case);
        }
        return strtolower($snake_case);
    }

    private function pluralize(string $singular): string
    {
        switch (substr($singular, -1)) {
            case "s":
                return "{$singular}es";
            case "y":
                return preg_replace('/y$/', "ies", $singular);
            default:
                return "{$singular}s";
        }
    }
}

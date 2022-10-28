<?php

namespace Battis\CRUD\SpecTraits;

use Exception;

trait MapProperties
{
    public static $DEFAULT_MAPPING = [];
    public static $MAPPING = "propertyToFieldMap";

    /** @var array */
    private $propertyToFieldMap;

    public function constructorHook(array $options)
    {
        $this->propertyToFieldMap = $this->computeMapping(
            $options[self::$MAPPING] ?? []
        );
    }

    private function computeMapping($propertyToFieldMap): array
    {
        // FIXME: needs verification
        return $propertyToFieldMap ?? static::$DEFAULT_MAPPING;
    }

    public function mapPropertiesToFields(array $data): array
    {
        $result = [];
        foreach ($data as $property => $value) {
            $field = $this->mapPropertyToField($property);
            if (isset($result[$field])) {
                throw new Exception(
                    "Duplicate field `$field` after mapping properties"
                );
            }
            $result[$field] = $value;
        }
        return $result;
    }

    public function mapFieldsToProperties(array $data): array
    {
        $result = [];
        foreach ($data as $field => $value) {
            $property = $this->mapFieldToProperty($field);
            if (isset($result[$property])) {
                throw new Exception(
                    "Duplicate property `$property` after mapping fields"
                );
            }
            $result[$property] = $value;
        }
        return $result;
    }

    public function mapPropertyToField(string $property): string
    {
        return $this->propertyToFieldMap[$property] ?? $property;
    }

    public function mapFieldToProperty(string $field): string
    {
        return array_search($field, $this->propertyToFieldMap) ?: $field;
    }
}

<?php

namespace Battis\CRUD\Traits\Spec;

use Battis\CRUD\Exceptions\SpecException;
use Battis\CRUD\Traits\MapProperties as MapPropertiesConstant;

trait MapProperties
{
    use MapPropertiesConstant;

    public static $DEFAULT_MAPPING = [];

    /** @var array */
    private $propertyToFieldMap;

    public function constructorHook(array $options = [])
    {
        $this->propertyToFieldMap = $this->computeMapping(
            $options[self::$MAPPING] ?? []
        );
    }

    private function computeMapping(array $propertyToFieldMap = null): array
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
                throw new SpecException(
                    "Duplicate field `$field` after mapping properties",
                    SpecException::MAPPING_ERROR
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
                throw new SpecException(
                    "Duplicate property `$property` after mapping fields",
                    SpecException::MAPPING_ERROR
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

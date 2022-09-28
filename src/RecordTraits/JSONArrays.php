<?php

namespace Battis\CRUD\RecordTraits;

use ReflectionClass;

trait JSONArrays
{
    protected static function objectToDatabaseHook(array $data): array
    {
        return array_combine(
            array_keys($data),
            array_map(
                fn($value) => is_array($value) ? json_encode($value) : $value,
                array_values($data)
            )
        );
    }

    protected static function databaseToObjectHook(array $data): array
    {
        $reflection = new ReflectionClass(static::class);
        $result = [];
        array_walk(function ($value, $key) use ($reflection, $result) {
            if (
                $reflection
                    ->getProperty($key)
                    ->getType()
                    ->getName() == "array"
            ) {
                $result[$key] = json_decode($value);
            } else {
                $result[$key] = $value;
            }
        }, $data);
        return $result;
    }
}

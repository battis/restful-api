<?php

namespace Battis\CRUD\Traits\Record;

use DateTime as GlobalDateTime;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;

trait DateTime
{
    protected static function objectToDatabaseHook(array $data): array
    {
        return array_combine(
            array_keys($data),
            array_map(
                fn($value) => $value instanceof DateTimeInterface
                    ? $value->format('Y-m-d H:i:s')
                    : $value,
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
                in_array(
                    $type = $reflection
                        ->getProperty($key)
                        ->getType()
                        ->getName(),
                    [GlobalDateTime::class, DateTimeImmutable::class]
                )
            ) {
                $result[$key] = new $type($value);
            } else {
                $result[$key] = $value;
            }
        }, $data);
        return $result;
    }
}

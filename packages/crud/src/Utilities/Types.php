<?php

namespace Battis\CRUD\Utilities;

use DateTimeInterface;
use Error;
use ReflectionMethod;
use ReflectionNamedType;

class Types
{
    public static function toDatabaseValue($value)
    {
        if (is_object($value) && $value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        } elseif (is_array($value)) {
            return json_encode($value);
        } else {
            return $value;
        }
    }

    public static function toDatabaseValues(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = self::toDatabaseValue($value);
        }
        return $result;
    }

    public static function toExpectedArgumentType(
        object $obj,
        string $method,
        $arg,
        int $argIndex = 0
    ) {
        $params = (new ReflectionMethod($obj, $method))->getParameters();
        if (isset($params[$argIndex])) {
            /** @var ReflectionNamedType $type */
            $type = $params[$argIndex]->getType();
            if ($type) {
                $class = $type->getName();
                if ($class == 'array') {
                    return json_decode($arg) ?? [];
                }
                try {
                    return new $class($arg);
                } catch (Error $e) {
                    // ignore
                }
            }
        }
        return $arg;
    }
}

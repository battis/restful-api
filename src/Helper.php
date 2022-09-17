<?php

namespace Battis\CRUD;

class Helper
{
    public static function snakeCase_to_PascalCase($snake_case)
    {
        return join(
            array_map(
                fn($token) => strtoupper(substr($token, 0, 1)) .
                    substr($token, 1),
                explode("_", $snake_case)
            )
        );
    }

    public static function camelCase_to_snake_case($camelCase)
    {
        return strtolower(
            preg_replace(
                "/([^A-Z])([A-Z])/g",
                '$1_$2',
                preg_replace("/([^0-9])([0-9])/g", '$1_$2', $camelCase)
            )
        );
    }

    public static function pluralize($singular)
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

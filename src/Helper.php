<?php

namespace Battis\CRUD;

class Helper
{
    public static function camelCase_to_snake_case($camelCase)
    {
        $snake_case = $camelCase;
        foreach (
            [
                "/([^0-9])([0-9])/", // separate numeric phrases
                "/([A-Z])([A-Z][a-z])/", // separate trailing word from acronym
                "/([^A-Z])([A-Z])/", // separate end of world from trailing word
            ]
            as $regexp
        ) {
            $snake_case = preg_replace($regexp, "$1_$2", $snake_case);
        }
        return strtolower($snake_case);
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
